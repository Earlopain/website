<?php
if (!isset($argv)) {
    require_once "htmlHelper.php";
    redirectToFolderOfFile();
}
$json = json_decode(base64_decode($argv[1]));
if($json->action !== "validatePassword") {
    dropPrivileges($json->uid);
}
switch ($json->action) {
    case "getdir":
        getDir($json->folder);
        break;
    case "getsinglefile":
        getSingleFile($json->folder, $json->id);
        break;
    case "getmime":
        require_once "getFolderInfo.php";
        $dir = new DirectoryInfo($json->folder, [$json->id]);
        $file = $dir->entries[0];
        echo mime_content_type($file->absolutePath);
        break;
    case "validatePassword":
        login($json->user, $json->password);
        break;
    case "zipselection":
        zipSelection($json->folder, $json->ids);
        break;
}

function getDir($path) {
    require_once "getFolderInfo.php";
    $dir = new DirectoryInfo($path);
    echo json_encode($dir);
}

function getSingleFile($folder, $id) {
    require_once "getFolderInfo.php";
    $dir = new DirectoryInfo($folder, [$id]);
    $file = $dir->entries[0];
    $stdin = fopen('php://stdin', 'r');
    $fd = fopen($file->absolutePath, "r");
    while (true) {
        $start = intval(fgets($stdin));
        $length = intval(fgets($stdin));
        if ($start < 0 || $length < 1) {
            break;
        }
        fseek($fd, $start);
        fwrite(STDOUT, fread($fd, $length));
        fflush(STDOUT);
    }
}

function login($user, $password) {
    set_error_handler(function () {die("false");}, E_ALL);
    $file = fopen("/etc/shadow", "r");
    if ($file === false) {
        loginFail($file);
    }
    while (!feof($file)) {
        $line = fgets($file);
        $split = explode(":", $line);
        if ($split[0] === $user) {
            $passwordSplit = explode("$", $split[1]);
            $algorithm = $passwordSplit[1];
            $salt = $passwordSplit[2];
            $compareAgainst = crypt($password, "$" . $algorithm . "$" . $salt . "$");
            if (strcmp($compareAgainst, $split[1]) !== 0) {
                loginFail($file);
            }
            echo posix_getpwnam($user)["uid"];
            exit();
        }
    }
    loginFail($file);
}
function loginFail($file) {
    echo "false";
    if ($file !== false) {
        fclose($file);
    }
    exit();
}

function zipSelection($path, $ids) {
    require_once "getFolderInfo.php";
    $dir = new DirectoryInfo($path, explode(",", $ids));
    $zipPath = tempnam(sys_get_temp_dir(), "zipdownload");
    $dir = new DirectoryInfo($path, explode(",", $ids));
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::OVERWRITE | ZipArchive::CREATE);
    foreach ($dir->entries as $file) {
        if (!$file->infoObject->isReadable()) {
            continue;
        }
        if (!$file->isDir) {
            $zip->addFile($file->absolutePath, $file->fileName);
        } else {
            $folderPath = dirname($file->absolutePath);
            $offset = strlen(substr($file->absolutePath, 0, strlen($folderPath)));
            $subdir = new RecursiveDirectoryIterator($file->absolutePath, RecursiveDirectoryIterator::SKIP_DOTS);
            $subdirfiles = new RecursiveIteratorIterator($subdir, RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($subdirfiles as $subfile) {
                if (!$subfile->isReadable() || $subfile->isDir()) {
                    continue;
                }
                $realpath = $subfile->getRealPath();
                $zip->addFile($realpath, substr($realpath, $offset));
            }
        }
    }
    $zip->close();
    flush();
    echo $zipPath;
}

function dropPrivileges($uid) {
    $userContext = posix_getpwuid($uid);
    posix_setgid($userContext["gid"]);
    posix_initgroups($userContext["name"], $userContext["gid"]);
    posix_setuid($uid);
}
