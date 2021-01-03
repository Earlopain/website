<?php

class E621Dl extends Program {
    protected function getCommand($extraData) {
        return [
            "node", "/media/plex/software/e621downloader.js", $extraData
        ];
    }
}
