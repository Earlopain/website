<?php

foreach (glob(__DIR__ . "/programs/*.php") as $file) {
    require_once $file;
}

class ProgramFactory {
    public static function getByName($name): Program {
        switch ($name) {
            case "plexfixdates":
                return new PlexFixDates();
            case "startplex":
                return new PlexStart();
            case "stoplex":
                return new PlexStop();
            case "restartplex":
                return new RestartPlex();
            case "apacherestart":
                return new ApacheRestart();
            case "youtube":
                return new YoutubeNormal();
            case "musicvideo":
                return new YoutubeMusicVideo();
            case "shortmovie":
                return new YoutubeShortMovies();
            case "plexrefreshcomics":
                return new E621RefreshPools();
            case "plextagimages":
                return new PlexTagImages();
            case "deezerdl":
                return new DeezerDl();
            case "e621dl":
                return new E621Dl();
            case "e621replace":
                return new E621Replace();
            case "sofurryepub":
                return new SofurryEpub();
            default:
                throw new Error("Unknown program " . $name);
                break;
        }
    }
}
