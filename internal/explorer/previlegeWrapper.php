<?php
if (!isset($_REQUEST{"action"})) {
    die("Action missing");
}
switch ($_REQUEST["action"]) {
    case "validatePassword":
        $result = sudoExec($_REQUEST["action"], $_REQUEST["user"], $_REQUEST["password"]);
        echo $result;
        break;
}

function sudoExec($action, ...$args) {
    $argString = "";
    foreach ($args as $string) {
        $argString .= "'" . prepareString($string) . "' ";
    }
    $action = prepareString($action);
    return shell_exec("sudo php -f /media/plex/html/internal/explorer/sudoScript.php '{$action}' " . $argString);
}

function prepareString(string $string) {
    $split = explode("'", $string);
    return implode("'\\''", $split);
}
