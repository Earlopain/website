<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    require_once "secret.php";
    include $_SERVER['DOCUMENT_ROOT'] . '/proxy.php';

    $json = file_get_contents('php://input');
    $obj = json_decode($json, true);
    if (!isset($obj)) {
        echo "No data";
        return;
    }
    if (!isset($obj["url"])) {
        echo "No url specified";
        return;
    }
    $response = proxyGetUrl($obj["url"] . "&key=" . Secret::get("steam"));
    echo $response;
}
