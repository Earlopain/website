<?php

class PlexStop extends Program {
    protected function getCommand($extraData) {
        return [
            "sudo", "/usr/bin/systemctl", "stop", "plexmediaserver"
        ];
    }

    protected function before($extraData) {
        echo "Stopping Plex\n";
    }

    protected function after($extraData) {
        echo "Plex Stopped\n";
    }
}
