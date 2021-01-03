<?php

class YoutubeShortMovies extends Youtube {
    protected function getPath() {
        return "/media/plex/plexmedia/shortmovies/%(title)s.%(ext)s";
    }
}
