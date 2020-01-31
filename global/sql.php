<?php
require_once "secretParser.php";

class SqlConnection {
    public static function get($dbname): PDO {
        $servername = "localhost";
        $username = "earlopain";
        $password = getSecret("dbpass");
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    }
}
