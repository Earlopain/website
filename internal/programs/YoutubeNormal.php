<?php

class YoutubeNormal extends Youtube {
    protected function getPath() {
        return "/media/plex/plexmedia/youtube/%(uploader)s/%(title)s.%(ext)s";
    }
}
