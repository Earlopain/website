<?php

class PlexFixDates extends Program {
    protected function getCommand($extraData) {
        return [
            "sudo", "/usr/bin/node", "/media/plex/software/plexFixDateAdded.js"
        ];
    }
    protected $programBefore = "plexstop";
    protected $programAfter = "plexstart";
}
