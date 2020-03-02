<?php
require_once "secret.php";
require_once "util.php";

$json = json_decode(file_get_contents('php://input'));
if (!isset($json->url)) {
    http_response_code(400);
    die();
}
$url = $json->url;

$domainWhitelist = ["api.steampowered.com", "api.twitter.com"];
$urlHost = parse_url($url, PHP_URL_HOST);

//Domain not in whitelist
if (array_search($urlHost, $domainWhitelist) === false) {
    http_response_code(403);
    die();
}

$header = [];
if (isset($json->type)) {
    $appendChar = parse_url($url, PHP_URL_QUERY) === null ? "?" : "&";
    switch ($json->type) {
        case "steam":
            $url .= $appendChar;
            $url .= "key=" . Secret::get("steam");
            break;
        case "twitter":
            $header["Authorization"] = "Bearer " . Secret::get("twitter_bearer_token");
            break;
        default:
            throw new Error("Unknown extra " . $json->type);
            break;
    }
}
echo getUrl($url, $header);
