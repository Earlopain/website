<?php

require_once "getfolderinfo.php";

switch ($_POST["action"]) {
    case 'getfolder':
        $dir = new DirectoryInfo($_REQUEST["path"]);
        echo json_encode($dir);
        break;
    case 'downloadselection':
        $dir = new DirectoryInfo($_POST["folder"], explode(",", $_POST["ids"]));
        var_dump($dir);
        break;
    default:
        # code...
        break;
}
