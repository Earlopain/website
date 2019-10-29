<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

if (isset($_POST["command"])) {
    while (@ ob_end_flush()); // end all output buffers if any
    $proc = popen(getCommand() , 'r');
    while (!feof($proc)) {
        echo fread($proc, 4096);
        @ flush();
    }
} elseif (isset($_GET["getfile"])) {
    echo file_get_contents($_GET["getfile"]);
} elseif (isset($_POST["savefile"]) && isset($_POST["savefiledata"])) {
    file_put_contents($_POST["savefile"], $_POST["savefiledata"]);
}


function getCommand()
{
    switch ($_POST["command"]) {
        case 'plexrestart':
            return "sudo service plexmediaserver restart";
        case 'plexrefreshcomics':
            return "node /media/plex/cronjobs/e621comics/e621PoolDownloader.js";
        case 'plextagimages':
            return "node /media/plex/cronjobs/filetagger/plexTagNewImages.js";
        case 'plexfixdates':
            return "echo 'Stopping Server' && sudo service plexmediaserver stop && echo 'Server Stopped' && sudo node /media/plex/software/plexFixDateAdded.js && echo 'Starting Server' && sudo service plexmediaserver start && echo 'Server Started'";
        case 'apache2restart':
            return "sudo service apache2 restart";
        case 'deezerdl':
            $myfile = fopen("/media/plex/software/deezerdl/downloadLinks.txt", "w") or die("Unable to open file!");
            fwrite($myfile, implode("\n", explode("|" , $_POST["link"])));
            fclose($myfile);
            return "cd /media/plex/software/deezerdl && ./SMLoader -q MP3_320 -p /media/plex/plexmedia/Music -d all";
        case 'e621dl':
            return "node /media/plex/software/e621downloader.js '" . $_POST["link"] . "'";
        case 'musicvideo':
            $filePath = "/media/plex/software/tempfiles/youtubedl.txt";
            $myfile = fopen($filePath, "w") or die("Unable to open file!");
            fwrite($myfile, $_POST["link"]);
            fclose($myfile);
            return "youtube-dl --write-thumbnail --no-cache-dir --no-playlist --batch-file {$filePath} -o '/media/plex/plexmedia/musicvideos/%(title)s.%(ext)s' && echo 'Done'";
        case 'plexfixnames':
            return "sudo service plexmediaserver stop && sudo node /media/plex/software/plexFixFileNames.js && sudo service plexmediaserver start && echo 'Done'";
        default:
            return "echo test";
    }
}
