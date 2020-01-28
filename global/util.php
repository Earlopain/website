<?php
function getJson($url, $header = []) {
    $headerArray = [];
    foreach ($header as $key => $value) {
        $headerArray[] = $key . ": " . $value;
    }
    $context = stream_context_create(["http" => ["header" => $headerArray]]);
    $result = file_get_contents($url, false, $context);
    return json_decode($result);
}

function createDirIfNotExists($path) {
    if (!file_exists($path)) {
        $result = mkdir($path);
        if ($result === false) {
            throw new Error("Failed to create " . $path);
        }
    }
}
