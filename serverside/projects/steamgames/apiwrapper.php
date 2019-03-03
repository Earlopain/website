<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include $_SERVER['DOCUMENT_ROOT'].'/serverside/secretParser.php';
    include $_SERVER['DOCUMENT_ROOT'].'/serverside/proxy.php';

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
    $response = proxyGetUrl($obj["url"]."&key=".getSecret("steam"));
    echo $response;
}