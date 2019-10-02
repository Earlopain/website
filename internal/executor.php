<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

if (isset($_GET["command"])) {
    while (@ ob_end_flush()); // end all output buffers if any
    $proc = popen(getCommand(), 'r');
    while (!feof($proc)) {
        echo fread($proc, 4096);
        @ flush();
    }
} elseif (isset($_REQUEST["getfile"])) {
    echo file_get_contents($_REQUEST["getfile"]);
} elseif (isset($_POST["savefile"]) && isset($_POST["savefiledata"])) {
    file_put_contents($_REQUEST["savefile"], $_REQUEST["savefiledata"]);
}


function getCommand()
{
    switch ($_REQUEST["command"]) {
        case 'plexrestart':
            return "sudo service plexmediaserver restart";
        case 'plexrefreshcomics':
            return "node /media/plex/cronjobs/e621comics/e621PoolDownloader.js";
        case 'plextagimages':
            return "node /media/plex/cronjobs/filetagger/plexTagNewImages.js";
        case 'apache2restart':
            return "sudo service apache2 restart";
        case 'deezerdl':
            $myfile = fopen("/media/plex/software/deezerdl/downloadLinks.txt", "w") or die("Unable to open file!");
            fwrite($myfile, implode("\n", explode("|" , $_REQUEST["link"])));
            fclose($myfile);
            return "cd /media/plex/software/deezerdl && ./SMLoader -q MP3_320 -p /media/plex/plexmedia/Music -d all";
        case 'e621dl':
            return "node /media/plex/software/e621downloader.js '" . $_REQUEST["posts"] . "'";
        default:
            return "echo test";
    }
}
