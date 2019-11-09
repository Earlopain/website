<?php

require_once "getFolderInfo.php";

switch ($_POST["action"]) {
    case 'getfolder':
        $dir = new DirectoryInfo($_REQUEST["path"]);
        echo json_encode($dir);
        break;
    case 'downloadselection':
        $zipPath = tempnam(sys_get_temp_dir(), "zipdownload");
        header('Content-Type: application/zip');
        header("Content-Transfer-Encoding: binary");
        $dir = new DirectoryInfo($_POST["folder"], explode(",", $_POST["ids"]));
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE | ZipArchive::CREATE);
        foreach ($dir->entries as $file) {
            if (!$file->isDir) {
                $zip->addFile($file->absolutePath);
            }
        }
        $zip->close();
        flush();
        readfile($zipPath);
        unlink($zipPath);

        break;
    default:
        # code...
        break;
}
