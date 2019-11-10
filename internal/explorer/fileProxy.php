<?php

require_once "getFolderInfo.php";

$dir = new DirectoryInfo(base64_decode($_REQUEST["folder"]), [$_REQUEST["id"]]);
$file = $dir->entries[0];

header('Content-Type: ' . mime_content_type($file->absolutePath));
if ($_SERVER["REQUEST_METHOD"] !== "HEAD") {
    readfile($file->absolutePath);
}
