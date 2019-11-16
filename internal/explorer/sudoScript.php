<?php
if (!isset($argv)) {
    require_once "htmlHelper.php";
    redirectToFolderOfFile();
}
$argv = prepareArgs($argv);

switch (array_shift($argv)) {
    case "validatePassword":
        login($argv[0], $argv[1]);
        break;
    case "getdir":
        getdir($argv[0], $argv[1]);
        break;
    case "zipselection":
        zipSelection($argv[0], $argv[1], $argv[2]);
        break;
    case "getsinglefile":
        getSingleFile($argv[0], $argv[1], $argv[2]);
        break;
    case "getmime":
        require_once "getFolderInfo.php";
        $dir = new DirectoryInfo(intval($argv[0]), $argv[1], [$argv[2]]);
        $file = $dir->entries[0];
        echo mime_content_type($file->absolutePath);
        break;
}

function getSingleFile($uid, $folder, $id) {
    require_once "getFolderInfo.php";
    $dir = new DirectoryInfo($uid, $folder, [$id]);
    $file = $dir->entries[0];
    $stdin = fopen('php://stdin', 'r');
    $fd = fopen($file->absolutePath, "r");
    while (true) {
        $start = intval(fgets($stdin));
        if ($start === -1) {
            break;
        }
        $length = intval(fgets($stdin));
        fseek($fd, $start);
        fwrite(STDOUT, fread($fd, $length));
        fflush(STDOUT);
    }
}

function getdir($uid, $path) {
    require_once "getFolderInfo.php";
    $dir = new DirectoryInfo($uid, $path);
    echo json_encode($dir);
}

function zipSelection($uid, $path, $ids) {
    require_once "getFolderInfo.php";
    $dir = new DirectoryInfo($uid, $path, explode(",", $ids));
    $zipPath = tempnam(sys_get_temp_dir(), "zipdownload");
    $dir = new DirectoryInfo($uid, $path, explode(",", $ids));
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

function prepareArgs($args) {
    //Remove first parameter which is executing script file location
    array_shift($args);
    foreach ($args as $key => $value) {
        $args[$key] = base64_decode($value);
    }
    return $args;
}
