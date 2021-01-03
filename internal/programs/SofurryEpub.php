<?php

class SofurryEpub extends Program {
    protected function getCommand($extraData) {
        return [
            "node", "/media/plex/software/sofurryepub/main.js", $extraData
        ];
    }
}
