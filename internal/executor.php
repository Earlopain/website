<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

if (isset($_POST["command"])) {
    while (@ob_end_flush()); // end all output buffers if any
    //die(getCommand());
    $proc = popen(getCommand(), 'r');
    while (!feof($proc)) {
        echo fread($proc, 4096);
        @flush();
    }
} elseif (isset($_GET["getfile"])) {
    echo file_get_contents($_GET["getfile"]);
} elseif (isset($_POST["savefile"]) && isset($_POST["savefiledata"])) {
    file_put_contents($_POST["savefile"], $_POST["savefiledata"]);
}

function getCommand() {
    switch ($_POST["command"]) {
        case 'plexrestart':
            return "sudo service plexmediaserver restart";
        case 'plexrefreshcomics':
            return "node /media/plex/cronjobs/e621comics/e621PoolDownloader.js";
        case 'plextagimages':
            return "node /media/plex/cronjobs/filetagger/plexTagNewImages.js";
        case 'plexfixdates':
            return wrapPlexStop("sudo node /media/plex/software/plexFixDateAdded.js");
        case 'plexfixnames':
            return wrapPlexStop("sudo node /media/plex/software/plexFixFileNames.js");
        case 'apache2restart':
            return "sudo service apache2 restart";
        case 'deezerdl':
            $filePath = "/media/plex/software/deezerdl/downloadLinks.txt";
            file_put_contents($filePath, $_POST["link"]);
            return "cd /media/plex/software/deezerdl && ./SMLoader -q MP3_320 -p /media/plex/plexmedia/Music -d all";
        case 'e621dl':
            return "node /media/plex/software/e621downloader.js '" . $_POST["link"] . "'";
        case 'musicvideo':
            return youtubedl("/media/plex/plexmedia/musicvideos/%(title)s.%(ext)s");
        case 'shortmovie':
            return youtubedl("/media/plex/plexmedia/shortmovies/%(title)s.%(ext)s");
        default:
            return "echo test";
    }
}

function youtubedl($targetFormat) {
    $filePath = "/media/plex/software/tempfiles/youtubedl.txt";
    file_put_contents($filePath, $_POST["link"]);
    return "youtube-dl --write-thumbnail --no-cache-dir --no-playlist --batch-file {$filePath} -o '{$targetFormat}'";
}

function wrapPlexStop($command) {
    return "echo 'Stopping Server' && sudo service plexmediaserver stop && echo 'Server Stopped' && " . $command . " && echo 'Starting Server' && sudo service plexmediaserver start && echo 'Server Started'";
}
