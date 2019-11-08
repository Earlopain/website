<?php

$dir = new DirectoryInfo($_REQUEST["path"]);

echo json_encode($dir);



class DirectoryEntry
{
    public $fileName;
    public $isDir;
    public $size;
    public $perms;
    public $user;
    public $group;
    function __construct(SplFileInfo $fileInfo)
    {
        $this->fileName = $fileInfo->getBasename();
        $this->isDir = $fileInfo->isDir();
        $this->size = $this->isDir ? -1 : $this->formatBytes($fileInfo->getSize());
        $this->perms = substr(sprintf('%o', $fileInfo->getPerms()), -3);
        $this->user = posix_getpwuid($fileInfo->getOwner())["name"];
        $this->group = posix_getgrgid($fileInfo->getGroup())["name"];
    }

    function formatBytes($bytes)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

class DirectoryInfo
{
    public $entries = [];
    public $entriesCount = 0;
    public $parentFolder;

    function __construct($path)
    {
        if (is_readable($path)) {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $fileInfo) {
                if (!$fileInfo->isDot()) {
                    $this->entries[] = new DirectoryEntry($fileInfo);
                }
            }
        }
        $this->entriesCount = count($this->entries);
        $this->parentFolder = dirname($path);
    }
}
