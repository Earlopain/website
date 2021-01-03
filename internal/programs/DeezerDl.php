<?php

class DeezerDl extends Program {
    protected function getCommand($extraData) {
        return [
            "smloadr",
            "-q", "MP3_320",
            "-p", "/media/plex/plexmedia/music",
            "-d", "all"
        ];
    }

    protected function before($extraData) {
        $fileName = "downloadLinks.txt";
        $folder = posix_getpwuid(posix_getuid())["dir"] . "/.config/smloadr";
        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }
        file_put_contents($folder . "/" . $fileName, $extraData);
    }
}
