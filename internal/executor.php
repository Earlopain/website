<?php

require_once "Program.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["getfile"]) && isAllowedFile($_GET["getfile"])) {
        echo file_get_contents(fileToPath($_GET["getfile"]));
    }
} else if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data["command"])) {
        executeCommand($data["command"], @$data["link"]);
    } else if (isset($data["savefile"]) && isset($data["savefiledata"]) && isAllowedFile($data["savefile"])) {
        file_put_contents(fileToPath($data["savefile"]), $data["savefiledata"]);
    }
}

function executeCommand($commandId, $extraData) {
    ob_end_flush();
    ob_implicit_flush();
    $success = Program::execute($commandId, $extraData);
    if ($success === true) {
        echo "DONE";
    } else {
        echo "Something went wrong ):\nCommand: {$commandId}\n===INPUT===\n{$extraData}";
    }
}

function allowedFiles() {
    return [
        "e621pools" => "/media/plex/software/e621comics/pools.json",
        "smloadrconfig" => "/srv/http/.config/smloadr/SMLoadrConfig.json"
    ];
}

function isAllowedFile($input) {
    return array_search($input, array_keys(allowedFiles())) !== false;
}

function fileToPath($input) {
    return allowedFiles()[$input];
}
