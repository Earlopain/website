<?php
if (!isset($_REQUEST{"action"})) {
    require_once "htmlHelper.php";
    redirectToFolderOfFile();
}

$action = $_REQUEST["action"];
$actionClearText = base64_decode($_REQUEST["action"]);
checkSessionStatus($actionClearText);
switch ($actionClearText) {
    case "validatePassword":
        $result = sudoExec($action, $_REQUEST["user"], $_REQUEST["password"]);
        if ($result !== "false") {
            session_start();
            $_SESSION["uid"] = $result;
            $_SESSION["username"] = base64_decode($_REQUEST["user"]);
            $_SESSION['created'] = time();
        }
        echo $result;
        break;
    case "getdir":
        $folder = sudoExec($action, Session::getUid(), $_REQUEST["path"]);
        $result = new stdClass();
        $result->folder = json_decode($folder);
        $result->username = $_SESSION["username"];
        echo json_encode($result);
        break;
    case "zipselection":
        ignore_user_abort(true);
        set_time_limit(0);
        $tempFilePath = sudoExec($action, Session::getUid(), $_REQUEST["folder"], $_REQUEST["ids"]);
        if (connection_status() !== CONNECTION_NORMAL) {
            unlink($tempFilePath);
            break;
        }
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
        if (isset($_REQUEST["mimeonly"])) {
            echo $mimeType;
        } else {
            header("Content-Type: " . $mimeType);
            $chunkSize = 8192;
            $start = 0;
            $command = generateCommand(base64_encode("getsinglefile"), $uid, $_REQUEST["folder"], $_REQUEST["id"]);
            $process = proc_open($command, [0 => ["pipe", "r"], 1 => ["pipe", "w"]], $pipes);
            while (true) {
                fwrite($pipes[0], $start . "\n");
                fwrite($pipes[0], $chunkSize . "\n");
                $bits = fread($pipes[1], $chunkSize);
                echo $bits;
                @flush();
                if (strlen($bits) !== $chunkSize) {
                    fwrite($pipes[0], "-1\n");
                    fclose($pipes[0]);
                    fclose($pipes[1]);
                    proc_close($process);
                    break;
                }
                $start += $chunkSize;
            }
        }
        break;
}

class Session {
    protected static $uid;

    public static function getUid() {
        if (!isset(self::$uid)) {
            session_start();
            self::$uid = base64_encode($_SESSION["uid"]);
            session_write_close();
        }
        return self::$uid;
    }
}

function sudoExec(...$args) {
    return shell_exec(generateCommand(...$args));
}

function generateCommand(...$args) {
    $argString = "";
    foreach ($args as $string) {
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            die("Invalid base64 string\n" . $string);
        }
        $argString .= "'" . $string . "' ";
    }
    return "sudo php -f /media/plex/html/internal/explorer/sudoScript.php " . $argString;
}

function checkSessionStatus($action) {
    if($action === "validatePassword") {
        return;
    }
    session_start();
    if (!isset($_SESSION["uid"])) {
        die("Not logged in");
    }
    $lifetime = 3600;
    if (isset($_SESSION['lastactivity']) && (time() - $_SESSION['lastactivity'] > $lifetime)) {
        session_unset();
        session_destroy();
        header("Location: login.php");
    }
    $_SESSION['lastactivity'] = time();
    if (time() - $_SESSION['created'] > $lifetime) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    session_write_close();
}
