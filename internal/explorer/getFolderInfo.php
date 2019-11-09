<?php

class DirectoryEntry {
    public $fileName;
    public $absolutePath;
    public $index;
    public $isDir;
    public $size;
    public $perms;
    public $isWriteable;
    public $isReadable;
    public $user;
    public $group;
    public $infoObject;

    public function __construct(SplFileInfo $fileInfo, $realPath, $index) {
        $this->fileName = $fileInfo->getBasename();
        $this->absolutePath = $realPath;
        $this->index = $index;
        $this->isDir = $fileInfo->isDir();
        $this->size = $this->isDir ? -1 : $this->formatBytes($fileInfo->getSize());
        $this->perms = substr(sprintf('%o', $fileInfo->getPerms()), -3);
        $this->user = UserGroupCache::resolveUser($fileInfo->getOwner());
        $this->group = UserGroupCache::resolveUser($fileInfo->getGroup());
        $this->isReadable = $this->permissionCheck(2);
        $this->isWriteable = $this->permissionCheck(1);
        $this->infoObject = $fileInfo;
    }

    private function permissionCheck($position) {
        if ($this->user === UserGroupCache::getUser() && $this->perms {0} & (1 << $position)) {
            return true;
        } else if (in_array($this->group, UserGroupCache::getGroups()) && $this->perms {1} & (1 << $position)) {
            return true;
        } else if ($this->perms {2} & (1 << $position)) {
            return true;
        }
        return false;
    }

    public function formatBytes($bytes) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

class DirectoryInfo {
    public $entries = [];
    public $entriesCount = 0;
    public $currentFolder;

    public function __construct($path, $idList = []) {
        $getAll = count($idList) === 0;
        if (is_readable($path)) {
            $dir = new DirectoryIterator($path);
            $counter = 0;
            foreach ($dir as $fileInfo) {
                if ($getAll || array_search($counter, $idList) !== false) {
                    $realPath = $fileInfo->getRealPath();
                    if (!$fileInfo->isDot() && $realPath !== false) {
                        $this->entries[] = new DirectoryEntry($fileInfo, $realPath, $counter);
                    }
                }
                $counter++;
            }
        }
        $this->entriesCount = count($this->entries);
        $this->currentFolder = realpath($path);
    }
}

class UserGroupCache {
    protected static $userCache = [];
    protected static $groupCache = [];
    protected static $groups;
    protected static $execUser = "www-data";

    public static function resolveUser($uid) {
        if (!isset(self::$userCache[$uid])) {
            self::$userCache[$uid] = posix_getpwuid($uid)["name"];
        }
        return self::$userCache[$uid];
    }

    public static function resolveGroup($gid) {
        if (!isset(self::$groupCache[$gid])) {
            self::$groupCache[$gid] = posix_getgrgid($gid)["name"];
        }
        return self::$groupCache[$gid];
    }

    public function getUser() {
        return self::$execUser;
    }

    public static function getGroups() {
        if (!isset(self::$groups)) {
            $user = self::$execUser;
            $groups = substr(exec("groups {$user} | cut -d':' -f2"), 1);
            self::$groups = explode(" ", $groups);
        }
        return self::$groups;
    }
}
