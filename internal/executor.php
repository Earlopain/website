<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["getfile"]) && isAllowedFile($_GET["getfile"])) {
        echo file_get_contents(fileToPath($_GET["getfile"]));
    }
} else if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data["command"])) {
        executeCommand($data["command"], @$data["link"]);
    } else if (isset($data["savefile"]) && isset($data["savefiledata"]) && isAllowedFile($data["savefile"])) {
        file_put_contents(fileToPath($data["savefile"]), $data["savefiledata"]);
    }
}

function executeCommand($commandId, $extraData) {
    while (@ob_end_flush()); // end all output buffers if any
    $command = getCommand($commandId, $extraData);
    $descriptorspec = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"], // stderr
    ];
    $proc = proc_open($command, $descriptorspec, $pipes);

    if (is_resource($proc)) {
        fwrite($pipes[0], $extraData);
        fclose($pipes[0]);
        while (!feof($pipes[1])) {
            echo fread($pipes[1], 4096);
            @flush();
        }
        fclose($pipes[1]);
        echo stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($proc);
        echo "DONE";
    } else {
        echo "Failed to open process";
    }
}

function getCommand($command, $extraData) {
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
            file_put_contents($folder . "/" . $fileName, $extraData);
            return "smloadr -q MP3_320 -p /media/plex/plexmedia/music -d all";
        case "e621dl":
            return "node /media/plex/software/e621downloader.js '" . $extraData . "'";
        case "e621replace":
            return "node /media/plex/software/e621replacer.js '" . $extraData . "'";
        case "sofurryepub":
            return "node /media/plex/software/sofurryepub/main.js '" . $extraData . "'";
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

function youtubedl($targetFormat) {
    $format = youtubedlformat();
    return "export LC_ALL=en_US.UTF-8 && youtube-dl --write-thumbnail --no-cache-dir --no-playlist --batch-file - -o '{$targetFormat}' --format '{$format}' ";
}

function youtubedlformat() {
    //https://github.com/TheFrenchGhosty/TheFrenchGhostys-YouTube-DL-Archivist-Scripts
    return "(bestvideo[vcodec^=av01][height>=4320][fps>30]/bestvideo[vcodec^=vp9.2][height>=4320][fps>30]/bestvideo[vcodec^=vp9][height>=4320][fps>30]/bestvideo[vcodec^=avc1][height>=4320][fps>30]/bestvideo[height>=4320][fps>30]/bestvideo[vcodec^=av01][height>=4320]/bestvideo[vcodec^=vp9.2][height>=4320]/bestvideo[vcodec^=vp9][height>=4320]/bestvideo[vcodec^=avc1][height>=4320]/bestvideo[height>=4320]/bestvideo[vcodec^=av01][height>=2880][fps>30]/bestvideo[vcodec^=vp9.2][height>=2880][fps>30]/bestvideo[vcodec^=vp9][height>=2880][fps>30]/bestvideo[vcodec^=avc1][height>=2880][fps>30]/bestvideo[height>=2880][fps>30]/bestvideo[vcodec^=av01][height>=2880]/bestvideo[vcodec^=vp9.2][height>=2880]/bestvideo[vcodec^=vp9][height>=2880]/bestvideo[vcodec^=avc1][height>=2880]/bestvideo[height>=2880]/bestvideo[vcodec^=av01][height>=2160][fps>30]/bestvideo[vcodec^=vp9.2][height>=2160][fps>30]/bestvideo[vcodec^=vp9][height>=2160][fps>30]/bestvideo[vcodec^=avc1][height>=2160][fps>30]/bestvideo[height>=2160][fps>30]/bestvideo[vcodec^=av01][height>=2160]/bestvideo[vcodec^=vp9.2][height>=2160]/bestvideo[vcodec^=vp9][height>=2160]/bestvideo[vcodec^=avc1][height>=2160]/bestvideo[height>=2160]/bestvideo[vcodec^=av01][height>=1440][fps>30]/bestvideo[vcodec^=vp9.2][height>=1440][fps>30]/bestvideo[vcodec^=vp9][height>=1440][fps>30]/bestvideo[vcodec^=avc1][height>=1440][fps>30]/bestvideo[height>=1440][fps>30]/bestvideo[vcodec^=av01][height>=1440]/bestvideo[vcodec^=vp9.2][height>=1440]/bestvideo[vcodec^=vp9][height>=1440]/bestvideo[vcodec^=avc1][height>=1440]/bestvideo[height>=1440]/bestvideo[vcodec^=av01][height>=1080][fps>30]/bestvideo[vcodec^=vp9.2][height>=1080][fps>30]/bestvideo[vcodec^=vp9][height>=1080][fps>30]/bestvideo[vcodec^=avc1][height>=1080][fps>30]/bestvideo[height>=1080][fps>30]/bestvideo[vcodec^=av01][height>=1080]/bestvideo[vcodec^=vp9.2][height>=1080]/bestvideo[vcodec^=vp9][height>=1080]/bestvideo[vcodec^=avc1][height>=1080]/bestvideo[height>=1080]/bestvideo[vcodec^=av01][height>=720][fps>30]/bestvideo[vcodec^=vp9.2][height>=720][fps>30]/bestvideo[vcodec^=vp9][height>=720][fps>30]/bestvideo[vcodec^=avc1][height>=720][fps>30]/bestvideo[height>=720][fps>30]/bestvideo[vcodec^=av01][height>=720]/bestvideo[vcodec^=vp9.2][height>=720]/bestvideo[vcodec^=vp9][height>=720]/bestvideo[vcodec^=avc1][height>=720]/bestvideo[height>=720]/bestvideo[vcodec^=av01][height>=480][fps>30]/bestvideo[vcodec^=vp9.2][height>=480][fps>30]/bestvideo[vcodec^=vp9][height>=480][fps>30]/bestvideo[vcodec^=avc1][height>=480][fps>30]/bestvideo[height>=480][fps>30]/bestvideo[vcodec^=av01][height>=480]/bestvideo[vcodec^=vp9.2][height>=480]/bestvideo[vcodec^=vp9][height>=480]/bestvideo[vcodec^=avc1][height>=480]/bestvideo[height>=480]/bestvideo[vcodec^=av01][height>=360][fps>30]/bestvideo[vcodec^=vp9.2][height>=360][fps>30]/bestvideo[vcodec^=vp9][height>=360][fps>30]/bestvideo[vcodec^=avc1][height>=360][fps>30]/bestvideo[height>=360][fps>30]/bestvideo[vcodec^=av01][height>=360]/bestvideo[vcodec^=vp9.2][height>=360]/bestvideo[vcodec^=vp9][height>=360]/bestvideo[vcodec^=avc1][height>=360]/bestvideo[height>=360]/bestvideo[vcodec^=av01][height>=240][fps>30]/bestvideo[vcodec^=vp9.2][height>=240][fps>30]/bestvideo[vcodec^=vp9][height>=240][fps>30]/bestvideo[vcodec^=avc1][height>=240][fps>30]/bestvideo[height>=240][fps>30]/bestvideo[vcodec^=av01][height>=240]/bestvideo[vcodec^=vp9.2][height>=240]/bestvideo[vcodec^=vp9][height>=240]/bestvideo[vcodec^=avc1][height>=240]/bestvideo[height>=240]/bestvideo[vcodec^=av01][height>=144][fps>30]/bestvideo[vcodec^=vp9.2][height>=144][fps>30]/bestvideo[vcodec^=vp9][height>=144][fps>30]/bestvideo[vcodec^=avc1][height>=144][fps>30]/bestvideo[height>=144][fps>30]/bestvideo[vcodec^=av01][height>=144]/bestvideo[vcodec^=vp9.2][height>=144]/bestvideo[vcodec^=vp9][height>=144]/bestvideo[vcodec^=avc1][height>=144]/bestvideo[height>=144]/bestvideo)+(bestaudio[acodec^=opus]/bestaudio)/best";
}

function wrapPlexStop($command) {
    return "echo 'Stopping Server' && sudo systemctl stop plexmediaserver && echo 'Server Stopped' && " . $command . " && echo 'Starting Server' && sudo systemctl start plexmediaserver && echo 'Server Started'";
}

function allowedFiles() {
    return [
        "e621pools" => "/media/plex/software/e621comics/pools.json",
        "smloadrconfig" => "/srv/http/.config/smloadr/SMLoadrConfig.json"
    ];
}

function isAllowedFile($input) {
    return array_search($input, array_keys(allowedFiles())) !== false;
}

function fileToPath($input) {
    return allowedFiles()[$input];
}
