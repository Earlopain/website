<?php

class PlexTagImages extends Program {
    protected function getCommand($extraData) {
        return [
            "node", "/media/plex/software/filetagger/plexTagNewImages.js"
        ];
    }
}
