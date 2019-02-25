<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents('php://input');
    http_response_code(400);
    $obj = json_decode($json, true);
    if (!isset($obj)) {
        echo "No data";
        return;
    }
    if (!isset($obj["url"])) {
        echo "No url specified";
        return;
    }
    http_response_code(200);
    echo proxyGetUrl($obj["url"]);
}

function proxyGetUrl($url){
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0");
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
    $response =  curl_exec($c);
    return $response;
}