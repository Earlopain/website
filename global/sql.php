<?php
require_once "secret.php";

class SqlConnection {
    private static $connections = [];

    public static function get($dbname): PDO {
        if (!isset(self::$connections[$dbname])) {
            $servername = "localhost";
            $username = Secret::get("dbuser");
            $password = Secret::get("dbpass");
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            self::$connections[$dbname] = $conn;
        }
        return self::$connections[$dbname];
    }
}
