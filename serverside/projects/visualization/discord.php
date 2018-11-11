<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $json = file_get_contents('php://input');
    if (!isset($json)) {
        echo "no post data";
        return;
    }
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
    };
    addServer($inviteJSON["guild"]["id"], $inviteJSON["code"], $inviteJSON["guild"]["name"]);
    echo "Success";
}
//only gets called if server does not have a valid invite
function addServer($id, $invite, $name)
{
    $currentJSON = json_decode(file_get_contents("./tracking.json"), true);
    //if already exists but no valid invite, replace the one instead of appending a new
    foreach ($currentJSON["servers"] as $key => $server) {
        if ($server["id"] === $id && !$server["invite"]) {   //found a server missing an invite with the right id, so put it there
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

function isValidInvite($id)
{   //no code specified but doesn't return the same error as invalid so return prematurely
    if($id === "")
        return false;
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, "https://discordapp.com/api/v6/invite/".$id."?with_counts=true");
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    $json =  curl_exec($c);
    $jsonparsed;
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

function alreadyTracking($id)
{
    $currentJSON = json_decode(file_get_contents("./tracking.json"), true);

    foreach ($currentJSON["servers"] as $value) {
        if ($value["id"] === $id) {
            return true;
        }
    }
    return false;
}
