<?php
require_once "util.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents('php://input');
    //assume the worst, set 200 later, if checks pass
    http_response_code(400);
    $obj = json_decode($json, true);
    if (!isset($obj)) {
        echo "No json data";
        return;
    }
    if (!isset($obj["invite"])) {
        echo "No id specified";
        return;
    }
    $inviteJSON = isValidInvite($obj["invite"]);
    if (!$inviteJSON) {
        echo "Not a valid invite";
        return;
    }
    if (alreadyTracking($inviteJSON["guild"]["id"])) {
        echo "Already tracking that";
        return;
    }
    addServer($inviteJSON["guild"]["id"], $inviteJSON["code"], $inviteJSON["guild"]["name"]);
    http_response_code(200);
    echo "Success";
}
//only gets called if server does not have a valid invite
function addServer($id, $invite, $name) {
    $currentJSON = json_decode(file_get_contents("./tracking.json"), true);
    //if already exists but no valid invite, replace the one instead of appending a new
    foreach ($currentJSON["servers"] as $key => $server) {
        if ($server["id"] === $id && !$server["invite"]) { //found a server missing an invite with the right id, so put it there
            $currentJSON["servers"][$key]["invite"] = $invite;
            $currentJSON["servers"][$key]["name"] = $name;
            file_put_contents("./tracking.json", json_encode($currentJSON));
            return;
        }
    }
    $size = count($currentJSON["servers"]);
    $currentJSON["servers"][$size]["id"] = $id;
    $currentJSON["servers"][$size]["invite"] = $invite;
    $currentJSON["servers"][$size]["name"] = htmlspecialchars($name);
    file_put_contents("./tracking.json", json_encode($currentJSON));
}

function isValidInvite($id) { //no code specified but doesn't return the same error as invalid so return prematurely
    if ($id === "") {
        return false;
    }

    $json = getJson("https://discordapp.com/api/v6/invite/" . $id . "?with_counts=true");
    $jsonparsed = null;
    try {
        $jsonparsed = json_decode($json, true);
    } catch (Exception $e) {
        return false;
    }
    if ($jsonparsed["code"] === 10006) {
        return false;
    }
    return $jsonparsed;
}

function alreadyTracking($id) {
    $currentJSON = json_decode(file_get_contents("./tracking.json"), true);

    foreach ($currentJSON["servers"] as $value) {
        if ($value["id"] === $id && isset($value["invite"])) {
            return true;
        }
    }
    return false;
}
