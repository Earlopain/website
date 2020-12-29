<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["getfile"])) {
        echo file_get_contents($_GET["getfile"]);
    }
} else if ($_SERVER["REQUEST_METHOD"] === "POST ") {
    if (isset($_POST["command"])) {
        executeCommand($_POST["command"]);
    } else if (isset($_POST["savefile"]) && isset($_POST["savefiledata"])) {
        file_put_contents($_POST["savefile"], $_POST["savefiledata"]);
    }
}

function executeCommand($commandId) {
    while (@ob_end_flush()); // end all output buffers if any
    //die(getCommand());
    $command = addCommandFinish(getCommand($commandId));
    $proc = popen($command, 'r');
    while (!feof($proc)) {
        echo fread($proc, 4096);
        @flush();
    }
}

function getCommand($command) {
    switch ($command) {
        case "plexrestart":
            return "sudo systemctl restart plexmediaserver";
        case "plexrefreshcomics":
            return "node /media/plex/software/e621comics/e621PoolDownloader.js";
        case "plextagimages":
            return "node /media/plex/software/filetagger/plexTagNewImages.js";
        case "plexfixdates":
            return wrapPlexStop("sudo node /media/plex/software/plexFixDateAdded.js");
        case "apache2restart":
            return "sudo systemctl restart apache2";
        case "deezerdl":
            $fileName = "downloadLinks.txt";
            $folder = posix_getpwuid(posix_getuid())["dir"] . "/.config/smloadr";
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
            file_put_contents($folder . "/" . $fileName, $_POST["link"]);
            return "smloadr -q MP3_320 -p /media/plex/plexmedia/music -d all";
        case "e621dl":
            return "node /media/plex/software/e621downloader.js '" . $_POST["link"] . "'";
        case "e621replace":
            return "node /media/plex/software/e621replacer.js '" . $_POST["link"] . "'";
        case "musicvideo":
            return youtubedl("/media/plex/plexmedia/musicvideos/%(title)s.%(ext)s");
        case "shortmovie":
            return youtubedl("/media/plex/plexmedia/shortmovies/%(title)s.%(ext)s");
        case "youtube":
            return youtubedl("/media/plex/plexmedia/youtube/%(uploader)s/%(title)s.%(ext)s");
        default:
            return "echo invalid_command";
    }
}

function addCommandFinish($command) {
    return $command . " 2>&1 && echo DONE";
}

function youtubedl($targetFormat) {
    $filePath = "/media/plex/software/tempfiles/youtubedl.txt";
    file_put_contents($filePath, $_POST["link"]);
    return "export LC_ALL=en_US.UTF-8 && youtube-dl --write-thumbnail --no-cache-dir --no-playlist --batch-file {$filePath} -o '{$targetFormat}'";
}

function wrapPlexStop($command) {
    return "echo 'Stopping Server' && sudo systemctl stop plexmediaserver && echo 'Server Stopped' && " . $command . " && echo 'Starting Server' && sudo systemctl start plexmediaserver && echo 'Server Started'";
}
