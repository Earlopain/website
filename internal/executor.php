<?php

require_once "Program.php";
require_once "FileManager.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ignore_user_abort(true);
set_time_limit(0);

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET["fileid"])) {
        echo FileManager::get($_GET["fileid"]);
    }
} else if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data["command"])) {
        executeCommand($data["command"], @$data["link"]);
    } else if (isset($data["fileid"]) && isset($data["filecontent"])) {
        FileManager::put($data["fileid"], $data["filecontent"]);
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
