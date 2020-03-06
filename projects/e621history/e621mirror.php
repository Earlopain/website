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
    $jsonArray = getJson("https://e621.net/posts.json?tags=status:flagged&limit=320", ["user-agent" => "earlopain"]);
    if ($jsonArray === NETWORK_ERROR) {
        handleNetworkError();
        return;
    }
    foreach ($jsonArray->posts as $json) {
        if (savePost($connection, $json, $json->id) === POST_FILE_SUCCESS) {
            Logger::log(LOG_INFO, "Saved {$json->file->md5}");
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
    $jsonArray = getJson("https://e621.net/posts.json?before_id={$beforeId}&limit={$postCount}", ["user-agent" => "earlopain"]);
    if ($jsonArray === NETWORK_ERROR) {
        handleNetworkError();
        return;
    }
    $jsonArray = array_reverse($jsonArray->posts);
    foreach ($jsonArray as $index => $json) {
        $jsonArray[$json->id] = $json;
        unset($jsonArray[$index]);
    }

    $currentId = $startId;
    for ($i = 0; $i < $postCount; $i++) {
        if (isset($jsonArray[$currentId])) {
            savePost($connection, $jsonArray[$currentId], $currentId);
        } else {
            $json = getPostJson($currentId);
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
        switch ($fileSaved) {
            case POST_FILE_SUCCESS:
                echo ($id . " file\n");
                break;
            case POST_FILE_DELETED:
                echo $id . " deleted\n";
                break;
            case POST_FILE_RETRY:
                Logger::log(LOG_ERR, "Network error for {$json->file->md5}");
                $connection->commit();
                return savePost($connection, getPostJson($id), $id);
            case POST_FILE_ALREADY_DOWNLOADED:
                break;
            default:
                die("invalid POST_FILE constant");
                break;
        }
        $connection->commit();
        return $fileSaved;
    }
}

function getPostJson(int $id) {
    $url = "https://e621.net/posts.json?tags=id:{$id}%20status:any";
    $json = getJson($url, ["user-agent" => "earlopain"]);
    while ($json === NETWORK_ERROR) {
        handleNetworkError();
        $json = getJson($url, ["user-agent" => "earlopain"]);
    }
    if (isset($json->posts[0])) {
        return $json->posts[0];
    }
    return [];
}

function getLowestId(PDO $connection) {
    //https://stackoverflow.com/a/31558121
    $statement = $connection->prepare("SELECT MIN(id) + 1 FROM posts t1 WHERE NOT EXISTS ( SELECT 1 FROM posts t2 WHERE id = t1.id + 1 )");
    $statement->execute();
    return $statement->fetchColumn();
}

function getHighestAvailable() {
    $url = "https://e621.net/posts.json?limit=1";
    $json = getJson($url, ["user-agent" => "earlopain"]);
    return $json->posts[0]->id;
}

function handleNetworkError() {
    echo "Network Error\n";
    sleep(10);
}
