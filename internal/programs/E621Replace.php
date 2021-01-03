<?php

class E621Replace extends Program {
    protected function getCommand($extraData) {
        return [
            "node", "/media/plex/software/e621replacer.js", $extraData
        ];
    }
}
