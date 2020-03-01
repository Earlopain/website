<?php
require_once "sql.php";
require_once "util.php";
require_once "e621post.php";
require_once "logger.php";
$connection = SqlConnection::get("e621");
$logger = Logger::get("mirror.log");
while (true) {
    $jsonArray = getJson("https://e621.net/post/index.json?tags=status:flagged&limit=320", ["user-agent" => "earlopain"]);
    foreach ($jsonArray as $json) {
        $post = new E621Post($json);
        if ($post->hasFile($connection)) {
            continue;
        }
        $post->save($connection);
        if ($post->saveFile($connection)) {
            $logger->log(LogLevel::INFO, "Saved {$post->md5}");
        }
    }
    sleep(60);
}
