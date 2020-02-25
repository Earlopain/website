<?php
require_once "config.php";
class Secret {
    private static $cache;

    public static function get($configKey) {
        if (!isset(self::$cache)) {
            $json = json_decode(file_get_contents(Config::get("secretfile")));
            self::$cache = [];
            foreach ($json as $key => $value) {
                self::$cache[$key] = $value;
            }
        }
        if (!isset(self::$cache[$configKey])) {
            throw new Error("Invalid secret key " . $configKey);
        }
        return self::$cache[$configKey];
    }
}
