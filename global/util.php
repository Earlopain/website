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

/**
 * Close the connection but keep executing everything below it
 * Only works if nothing has been outputed yet
 * https://gist.github.com/bubba-h57/32593b2b970366d24be
 * @param  string  $body
 * @param  integer $responseCode
 * @return void
 */
function closeConnection(string $body = "", int $responseCode = 200) {
    if (headers_sent($file, $line)) {
        echo "Headers already send in {$file}:{$line}";
        die();
    }

    set_time_limit(0);
    ignore_user_abort(true);
    ob_end_clean();
    ob_start();
    echo $body;
    $size = ob_get_length();
    header("Connection: close\r\n");
    header("Content-Encoding: none\r\n");
    header("Content-Length: $size");
    http_response_code($responseCode);
    ob_end_flush();
    @ob_flush();
    flush();
}
