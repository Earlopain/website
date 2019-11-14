<?php
if (!isset($_REQUEST{"action"})) {
    require_once "htmlHelper.php";
    redirectToFolderOfFile();
}
$action = $_REQUEST["action"];
session_start();
switch (base64_decode($_REQUEST["action"])) {
    case "validatePassword":
        $result = sudoExec($action, $_REQUEST["user"], $_REQUEST["password"]);
        if ($result !== "false") {
            $_SESSION["uid"] = $result;
        }
        echo $result;
        break;
    case "getdir":
        $result = sudoExec($action, getUid(), $_REQUEST["path"]);
        echo $result;
        break;
    case "zipselection":
        $tempFilePath = sudoExec($action, getUid(), $_REQUEST["folder"], $_REQUEST["ids"]);
        $date = date_create();
        $filename = date_format($date, 'Y-m-d_H-i-s');
        header('Content-Type: application/zip');
        header("Content-Transfer-Encoding: binary");
        header('Content-Disposition: attachment; filename="' . $filename . '.zip"');
        readfile($tempFilePath);
        unlink($tempFilePath);
        break;
    case "getsinglefile":
        $uid = getUid();
        $mimeType = sudoExec(base64_encode("getmime"), $uid, $_REQUEST["folder"], $_REQUEST["id"]);
        echo $mimeType;
        header('Content-Type: ' . $mimeType);
        if (base64_decode($_REQUEST["mimeonly"] !== "true")) {
            readfile($file->absolutePath);
        }
        break;
}

function getUid() {
    if (!isset($_SESSION["uid"])) {
        die("Not logged in");
    }
    return base64_encode($_SESSION["uid"]);
}

function sudoExec(...$args) {
    $argString = "";
    foreach ($args as $string) {
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            die("Invalid base64 string\n" . $string);
        }
        $argString .= "'" . $string . "' ";
    }
    $string = "sudo php -f /media/plex/html/internal/explorer/sudoScript.php " . $argString;
    return shell_exec($string);
}
