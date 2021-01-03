<?php

class YoutubeMusicVideo extends Youtube {
    protected function getPath() {
        return "/media/plex/plexmedia/musicvideos/%(title)s.%(ext)s";
    }
}
