<?php

require_once "getFolderInfo.php";

switch ($_POST["action"]) {
    case 'getfolder':
        $dir = new DirectoryInfo($_REQUEST["path"]);
        echo json_encode($dir);
        break;
    case 'downloadselection':
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
