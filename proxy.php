<?php
require_once "secret.php";
$json = json_decode(file_get_contents('php://input'));
$url = $json->url;

$domainWhitelist = ["api.steampowered.com"];
$urlHost = parse_url($url, PHP_URL_HOST);

//Domain not in whitelist
if (array_search($urlHost, $domainWhitelist) === false) {
    http_response_code(403);
    die();
}

if (isset($json->type)) {
    $appendChar = parse_url($url, PHP_URL_QUERY) === null ? "?" : "&";
    $url .= $appendChar;
    switch ($json->type) {
        case "steam":
            $url .= "key=" . Secret::get("steam");
            break;
        default:
            throw new Error("Unknown extra " . $json->type);
            break;
    }
}
echo @file_get_contents($url);
