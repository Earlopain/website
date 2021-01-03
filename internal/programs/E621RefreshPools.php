<?php

class E621RefreshPools extends Program {
    protected function getCommand($extraData) {
        return [
            "node", "/media/plex/software/e621comics/e621PoolDownloader.js"
        ];
    }
}
