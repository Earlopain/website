<?php

class DirectoryEntry
{
    public $fileName;
    public $index;
    public $isDir;
    public $size;
    public $perms;
    public $user;
    public $group;
    function __construct(SplFileInfo $fileInfo, $index)
    {
        $this->fileName = $fileInfo->getBasename();
        $this->index = $index;
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

    function __construct($path, $idList = [])
    {
        $getAll = count($idList) === 0;
        if (is_readable($path)) {
            $dir = new DirectoryIterator($path);
            $counter = 0;
            foreach ($dir as $fileInfo) {
                if($getAll || array_search($counter, $idList) !== false)
                if (!$fileInfo->isDot() && $fileInfo->getRealPath() !== false) {
                    $this->entries[] = new DirectoryEntry($fileInfo, $counter);
                }
                $counter++;
            }
        }
        $this->entriesCount = count($this->entries);
        $this->parentFolder = dirname($path);
    }
}
