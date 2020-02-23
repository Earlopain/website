<?php
require_once "secretParser.php";

class SqlConnection {
    private static $connections = [];

    public static function get($dbname): PDO {
        if (!isset(self::$connections[$dbname])) {
            $servername = "localhost";
            $username = getSecret("dbuser");
            $password = getSecret("dbpass");
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$connections[$dbname] = $conn;
        }
        return self::$connections[$dbname];
    }
}