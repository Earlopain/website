<?php

$argv = prepareArgs($argv);

switch ($argv[1]) {
    case "validatePassword":
        login($argv);
        break;
    case "getdir":
        getdir($argv);
        break;
    case "zipselection":
        zipSelection($argv);
        break;
}

function getdir($argv) {
    require_once "getFolderInfo.php";
    $path = $argv[2];
    $uid = $argv[3];
    $dir = new DirectoryInfo($path, $uid);
    echo json_encode($dir);
}

function zipSelection($argv) {
    require_once "getFolderInfo.php";
    $path = $argv[2];
    $ids = $argv[3];
    $uid = $argv[4];
    $dir = new DirectoryInfo($path, $uid, explode(",", $ids));
    $zipPath = tempnam(sys_get_temp_dir(), "zipdownload");
    $dir = new DirectoryInfo($path, $uid, explode(",", $ids));
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::OVERWRITE | ZipArchive::CREATE);
    if ($dir->currentFolder !== "/") {
        array_shift($dir->entries);
    }
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

function login($argv) {
    set_error_handler(function() {die("false");}, E_ALL);
    $user = $argv[2];
    $password = $argv[3];
    $file = fopen("/etc/shadow", "r");
    if ($file === false) {
        exitProcess($file);
    }
    while (!feof($file)) {
        $line = fgets($file);
        $split = explode(":", $line);
        if ($split[0] === $user) {
            $passwordSplit = explode("$", $split[1]);
            $algorithm = $passwordSplit[1];
            $salt = $passwordSplit[2];
            $compairAgainst = exec("openssl passwd -{$algorithm} -salt '{$salt}' '{$password}'");
            if (strcmp($compairAgainst, $split[1]) !== 0) {
                exitProcess($file);
            }
            echo posix_getpwnam($user)["uid"];
            exit();
        }
    }
    exitProcess($file);
}

function exitProcess($file) {
    echo "false";
    if ($file !== false) {
        fclose($file);
    }
    exit();
}

function prepareArgs($args) {
    foreach ($args as $key => $value) {
        $args[$key] = base64_decode($value);
    }
    return $args;
}
