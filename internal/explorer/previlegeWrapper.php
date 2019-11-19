<?php

$json = base64_decode($_REQUEST["data"], true);

if ($json === false) {
    require_once "htmlHelper.php";
    redirectToFolderOfFile();
}

$json = json_decode($json);
Session::checkSessionStatus($json->action);
$json = jsonAddUid($json);

switch ($json->action) {
    case "validatePassword":
        $result = sudoExec($json);
        if ($result !== "false") {
            session_start();
            $_SESSION["uid"] = $result;
            $_SESSION["username"] = $json->user;
            $_SESSION['created'] = time();
        }
        echo $result;
        break;
    case "getdir":
        $folder = sudoExec($json);
        $result = new stdClass();
        $result->folder = json_decode($folder);
        $result->username = $_SESSION["username"];
        echo json_encode($result);
        break;
    case "zipselection":
        ignore_user_abort(true);
        set_time_limit(0);
        $tempFilePath = sudoExec($json);
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
        $mimeType = sudoExec($json, "getmime");
        if (isset($json->mimeonly)) {
            echo $mimeType;
        } else {
            header("Content-Type: " . $mimeType);
            $chunkSize = 8192;
            $start = 0;
            $command = generateCommand($json, "getsinglefile");
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
        return self::$uid;
    }

    public static function checkSessionStatus($action) {
        if ($action === "validatePassword") {
            return;
        }
        session_start();
        if (!isset($_SESSION["uid"])) {
            die("Not logged in");
        }
        self::$uid = $_SESSION["uid"];
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
}

function jsonAddUid($json) {
    $json->uid = Session::getUid();
    return $json;
}

function sudoExec($json, $actionOverwrite = null) {
    return shell_exec(generateCommand($json, $actionOverwrite));
}

function generateCommand($json, $actionOverwrite = null) {
    if ($actionOverwrite !== null) {
        $json->action = $actionOverwrite;
    }
    $data = base64_encode(json_encode($json));
    return "sudo php -f /media/plex/html/internal/explorer/sudoScript.php '{$data}'";
}
