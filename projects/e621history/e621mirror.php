<?php
require_once "sql.php";
require_once "util.php";
require_once "e621post.php";
require_once "logger.php";
$connection = SqlConnection::get("e621");
$highestId = getHighestAvailable($connection);
while (true) {
    echo "checking flagged\n";
    checkFlaggedPosts($connection);
    echo "getting next\n";
    getNextMissingPosts($connection, $highestId);
}

function checkFlaggedPosts(PDO $connection) {
    $logger = Logger::get("mirror.log");
    $jsonArray = getJson("https://e621.net/post/index.json?tags=status:flagged&limit=320", ["user-agent" => "earlopain"]);
    foreach ($jsonArray as $json) {
        $post = new E621Post($json);
        if ($post->hasFile($connection)) {
            continue;
        }
        $connection->beginTransaction();
        $post->save($connection);
        echo ($post->id . " json\n");
        if ($post->saveFile($connection)) {
            echo $post->id . " saved\n";
            $logger->log(LogLevel::INFO, "Saved {$post->md5}");
        }
        $connection->commit();
    }
}

function getNextMissingPosts(PDO $connection, int $stopId) {
    $remaining = 10;
    for ($i = 0; $i < $remaining; $i++) {
        $id = getLowestId($connection);
        if ($id > $stopId) {
            return;
        }
        $url = "https://e621.net/post/show.json?id={$id}";
        $json = getJson($url, ["user-agent" => "earlopain"]);
        if ($json === false) {
            return;
        }
        if (!isset($json->id)) {
            echo ($id . " nuked\n");
            E621Post::saveNuked($connection, $id);
        } else {
            $connection->beginTransaction();
            $post = new E621Post($json);
            $post->save($connection);
            echo ($id . " json\n");
            if ($post->saveFile($connection)) {
                echo ($id . " file\n");
            }
            $connection->commit();
        }
    }
}

function getLowestId(PDO $connection) {
    //https://stackoverflow.com/a/31558121
    $statement = $connection->prepare("SELECT MIN(id) + 1 FROM posts t1 WHERE NOT EXISTS ( SELECT 1 FROM posts t2 WHERE id = t1.id + 1 )");
    $statement->execute();
    return $statement->fetchColumn();
}

function getHighestAvailable() {
    $url = "https://e621.net/post/index.json?limit=1";
    $json = getJson($url, ["user-agent" => "earlopain"]);
    return $json[0]->id;
}
