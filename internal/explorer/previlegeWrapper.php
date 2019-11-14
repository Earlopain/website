<?php
if (!isset($_REQUEST{"action"})) {
    require_once "htmlHelper.php";
    redirectToFolderOfFile();
}
$action = $_REQUEST["action"];
switch (base64_decode($_REQUEST["action"])) {
    case "validatePassword":
        $result = sudoExec($action, $_REQUEST["user"], $_REQUEST["password"]);
        if ($result !== "false") {
            $_SESSION["uid"] = $result;
        }
        echo $result;
        break;
    case "getdir":
        $result = sudoExec($action, Session::getUid(), $_REQUEST["path"]);
        echo $result;
        break;
    case "zipselection":
        $tempFilePath = sudoExec($action, Session::getUid(), $_REQUEST["folder"], $_REQUEST["ids"]);
        $date = date_create();
        $filename = date_format($date, 'Y-m-d_H-i-s');
        header('Content-Type: application/zip');
        header("Content-Transfer-Encoding: binary");
        header('Content-Disposition: attachment; filename="' . $filename . '.zip"');
        readfile($tempFilePath);
        unlink($tempFilePath);
        break;
    case "getsinglefile":
        $uid = Session::getUid();
        $mimeType = sudoExec(base64_encode("getmime"), $uid, $_REQUEST["folder"], $_REQUEST["id"]);
        if (base64_decode($_REQUEST["mimeonly"]) === "true") {
            echo $mimeType;
        } else {
            header('Content-Type: ' . $mimeType);
            $chunkSize = 1024 * pow(8, 1);
            $start = 0;
            while (true) {
                $bits = sudoExec(base64_encode("getsinglefile"), $uid, $_REQUEST["folder"], $_REQUEST["id"], base64_encode($start), base64_encode($chunkSize));
                echo $bits;
                if (strlen($bits) !== $chunkSize) {
                    break;
                }
                @flush();
                $start += $chunkSize;
            }
        }
        break;
}

class Session {
    protected static $uid;

    public static function getUid() {
        if(!isset(self::$uid)){
            session_start();
            if (!isset($_SESSION["uid"])) {
                die("Not logged in");
            }
            self::$uid = base64_encode($_SESSION["uid"]);
            session_write_close();
        }
        return self::$uid;
    }
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
