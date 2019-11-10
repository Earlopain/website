<?php

require_once "getFolderInfo.php";

switch ($_POST["action"]) {
    case 'getfolder':
        $dir = new DirectoryInfo($_REQUEST["path"]);
        echo json_encode($dir);
        break;
    case 'downloadselection':
        createZipAndEcho();
        break;
}

function createZipAndEcho() {
    $zipPath = tempnam(sys_get_temp_dir(), "zipdownload");
    $date = date_create();
    $filename = date_format($date, 'Y-m-d_H-i-s');
    header('Content-Type: application/zip');
    header("Content-Transfer-Encoding: binary");
    header('Content-Disposition: attachment; filename="' . $filename . '.zip"');
    $dir = new DirectoryInfo($_POST["folder"], explode(",", $_POST["ids"]));
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
    readfile($zipPath);
    unlink($zipPath);
}
