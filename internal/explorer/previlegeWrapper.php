<?php
if (!isset($_REQUEST{"action"})) {
    die("Action missing");
}
session_start();
switch ($_REQUEST["action"]) {
    case "validatePassword":
        $result = sudoExec($_REQUEST["user"], $_REQUEST["password"]);
        if ($result !== "false") {
            session_start();
            $_SESSION["uid"] = $result;
        }
        break;
    case "getdir":
        $result = sudoExec($_REQUEST["path"], getUid());
        echo $result;
        break;
}

function getUid() {
    return isset($_SESSION["uid"]) ? $_SESSION["uid"] : posix_getpwnam("nobody")["uid"];
}

function sudoExec(...$args) {
    $argString = "";
    foreach ($args as $string) {
        $argString .= "'" . prepareString($string) . "' ";
    }
    $action = prepareString($_REQUEST["action"]);
    return shell_exec("sudo php -f /media/plex/html/internal/explorer/sudoScript.php '{$action}' " . $argString);
}

function prepareString(string $string) {
    $split = explode("'", $string);
    return implode("'\\''", $split);
}
