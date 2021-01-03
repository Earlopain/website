<?php

class RestartPlex extends Program {
    protected function getCommand($extraData) {
        return [
            "sudo", "/usr/bin/systemctl", "restart", "plexmediaserver"
        ];
    }
}
