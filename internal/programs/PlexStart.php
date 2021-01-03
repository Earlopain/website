<?php

class PlexStart extends Program {
    protected function getCommand($extraData) {
        return [
            "sudo", "/usr/bin/systemctl", "start", "plexmediaserver"
        ];
    }

    protected function before($extraData) {
        echo "Starting Plex\n";
    }

    protected function after($extraData) {
        echo "Plex Started\n";
    }
}
