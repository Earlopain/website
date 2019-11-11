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
    $command = "";
    switch ($_POST["command"]) {
        case 'plexrestart':
            $command = "sudo service plexmediaserver restart";
            break;
        case 'plexrefreshcomics':
            $command = "node /media/plex/cronjobs/e621comics/e621PoolDownloader.js";
            break;
        case 'plextagimages':
            $command = "node /media/plex/cronjobs/filetagger/plexTagNewImages.js";
            break;
        case 'plexfixdates':
            $command = wrapPlexStop("sudo node /media/plex/software/plexFixDateAdded.js");
            break;
        case 'apache2restart':
            $command = "sudo service apache2 restart";
            break;
        case 'deezerdl':
            $filePath = "/media/plex/software/deezerdl/downloadLinks.txt";
            file_put_contents($filePath, $_POST["link"]);
            $command = "cd /media/plex/software/deezerdl && ./SMLoader -q MP3_320 -p /media/plex/plexmedia/Music -d all";
            break;
        case 'e621dl':
            $command = "node /media/plex/software/e621downloader.js '" . $_POST["link"] . "'";
            break;
        case 'musicvideo':
            $command = youtubedl("/media/plex/plexmedia/musicvideos/%(title)s.%(ext)s");
            break;
        case 'shortmovie':
            $command = youtubedl("/media/plex/plexmedia/shortmovies/%(title)s.%(ext)s");
            break;
        default:
            $command = "echo test";
            break;
    }
    return $command . " 2>&1 && echo DONE";
}

function youtubedl($targetFormat) {
    $filePath = "/media/plex/software/tempfiles/youtubedl.txt";
    file_put_contents($filePath, $_POST["link"]);
    return "export LC_ALL=en_US.UTF-8 && youtube-dl --write-thumbnail --no-cache-dir --no-playlist --batch-file {$filePath} -o '{$targetFormat}'";
}

function wrapPlexStop($command) {
    return "echo 'Stopping Server' && sudo service plexmediaserver stop && echo 'Server Stopped' && " . $command . " && echo 'Starting Server' && sudo service plexmediaserver start && echo 'Server Started'";
}
