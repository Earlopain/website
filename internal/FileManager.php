<?php

class FileManager {
    public static function get($name) {
        if (self::isAllowedFile($name)) {
            return file_get_contents(self::fileToPath($name));
        } else {
            throw new Error("Dissallowed");
        }
    }

    public static function put($name, $content) {
        if (self::isAllowedFile($name)) {
            file_put_contents(self::fileToPath($name), $content);
        } else {
            throw new Error("Dissallowed");
        }
    }

    private static function getAllowedFiles() {
        return [
            "e621pools" => "/media/plex/software/e621comics/pools.json",
            "smloadrconfig" => "/srv/http/.config/smloadr/SMLoadrConfig.json"
        ];
    }

    private static function isAllowedFile($input) {
        return array_search($input, array_keys(self::getAllowedFiles())) !== false;
    }

    private static function fileToPath($input) {
        return self::getAllowedFiles()[$input];
    }
}
