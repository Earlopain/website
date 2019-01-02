<?php
set_time_limit(10);   //1h
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents('php://input');
    http_response_code(400);
    $obj = json_decode($json, true);
    if (!isset($obj)) {
        echo "No post data";
        return;
    }
    if (!isset($obj["username"])) {
        echo "No username specified";
        return;
    }
    if (!isValidUsername($obj["username"])) {
        echo "Not a valid username";
        return;
    }
    $favs = shell_exec("node ./getUserFavs.js " . $obj["username"]);
    http_response_code(200);
    echo json_encode($favs);

}

function getFavs($username)
{
    $result = [];
    $c = curl_init();
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_USERAGENT, "favhistory/earlopain");
    $page = 1;
    while (true) {
        curl_setopt($c, CURLOPT_URL, "https://e621.net/post/index.json?tags=fav:$username&limit=320&page=$page");
        $json =  json_decode(curl_exec($c), true);
        foreach ($json as $index => $item) {
            array_push($result, $item);
        }
        if (sizeof($json) !== 320) {
            return $result;
        }
        $page++;
    }
}

function addMissingFavsToDB($favs)
{
    $writeToFile = false;
    $json  = json_decode(file_get_contents("./posts.json"), true);
    foreach ($favs as $index => $post) {
        if (!isset($json[$post["md5"]])) {
            $writeToFile = true;
            $json[$post["md5"]] = $post;
        }
    }
    if ($writeToFile) {
        file_put_contents("./posts.json", json_encode($json));
    }
}

function isValidUsername($username)
{
    if ($username === "") {
        return false;
    }
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, "https://e621.net/user/show.json?name=".$username);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_USERAGENT, "favhistory/earlopain");
    $json =  curl_exec($c);
    $responsecode = curl_getinfo($c, CURLINFO_HTTP_CODE);
    if ($responsecode === 302) {   //redirect, only if user not exists
        return false;
    }
    return true;
}
