<?php

class Config {
    private static $cache;

    public static function get($configKey) {
        if (!isset(self::$cache)) {
            $json = json_decode(file_get_contents(__DIR__ . "/../config.json"));
            self::$cache = [];
            foreach ($json as $key => $value) {
                self::$cache[$key] = $value;
            }
        }
        if (!isset(self::$cache[$configKey])) {
            throw new Error("Invalid config key " . $configKey);
        }
        return self::$cache[$configKey];
    }
}
