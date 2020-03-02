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
        if (savePost($connection, $json, $json->id)) {
            $logger->log(LogLevel::INFO, "Saved {$json->md5}");
        }
    }
}

function getNextMissingPosts(PDO $connection, int $stopId) {
    $postCount = 10;
    $startId = getLowestId($connection);
    if ($startId - 1 === $stopId) {
        return;
    }
    $beforeId = $startId + $postCount;
    $jsonArray = getJson("https://e621.net/post/index.json?before_id={$beforeId}&limit={$postCount}", ["user-agent" => "earlopain"]);
    $jsonArray = array_reverse($jsonArray);
    foreach ($jsonArray as $index => $json) {
        $jsonArray[$json->id] = $json;
        unset($jsonArray[$index]);
    }

    $currentId = $startId;
    for ($i = 0; $i < $postCount; $i++) {
        if (isset($jsonArray[$currentId])) {
            savePost($connection, $jsonArray[$currentId], $currentId);
        } else {
            $url = "https://e621.net/post/show.json?id={$currentId}";
            $json = getJson($url, ["user-agent" => "earlopain"]);
            savePost($connection, $json, $currentId);
        }
        $currentId++;
    }
}

function savePost(PDO $connection, $json, $id) {
    if (!isset($json->id)) {
        echo ($id . " nuked\n");
        E621Post::saveNuked($connection, $id);
        return false;
    } else {
        $connection->beginTransaction();
        $post = new E621Post($json);
        $post->save($connection);
        $fileSaved = $post->saveFile($connection);
        if ($fileSaved) {
            echo ($id . " file\n");
        } else if (!$post->hasFile($connection)) {
            echo $id . " deleted\n";
        }
        $connection->commit();
        return $fileSaved;
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
