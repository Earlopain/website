<?php

class ApacheRestart extends Program {
    protected function getCommand($extraData) {
        return [
            "sudo", "/usr/bin/systemctl", "restart", "httpd"
        ];
    }
}
