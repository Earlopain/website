<?php
if (!isset($_REQUEST{"action"})) {
    die("Action missing");
}
session_start();
switch (base64_decode($_REQUEST["action"])) {
    case "validatePassword":
        $result = sudoExec($_REQUEST["user"], $_REQUEST["password"]);
        if ($result !== "false") {
            $_SESSION["uid"] = $result;
        }
        break;
    case "getdir":
        $result = sudoExec($_REQUEST["path"], getUid());
        echo $result;
        break;
    case "zipselection":
        $tempFilePath = sudoExec($_REQUEST["folder"], $_REQUEST["ids"], getUid());
        $date = date_create();
        $filename = date_format($date, 'Y-m-d_H-i-s');
        header('Content-Type: application/zip');
        header("Content-Transfer-Encoding: binary");
        header('Content-Disposition: attachment; filename="' . $filename . '.zip"');
        readfile($tempFilePath);
        unlink($tempFilePath);
        break;
}

function getUid() {
    return base64_encode(isset($_SESSION["uid"]) ? $_SESSION["uid"] : posix_getpwnam("nobody")["uid"]);
}

function sudoExec(...$args) {
    $argString = "";
    foreach ($args as $string) {
        $argString .= "'" . $string . "' ";
    }
    $action = $_REQUEST["action"];
    $string = "sudo php -f /media/plex/html/internal/explorer/sudoScript.php '{$action}' " . $argString;
    return shell_exec($string);
}
