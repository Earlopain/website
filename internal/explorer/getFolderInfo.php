<?php
setlocale(LC_ALL, "en_US.UTF-8");
class DirectoryEntry {
    public $fileName;
    public $ext;
    public $absolutePath;
    public $index;
    public $isDir;
    public $size;
    public $perms;
    public $isReadable;
    public $isWriteable;
    public $isExecutable;
    public $user;
    public $group;
    public $infoObject;

    public function __construct(SplFileInfo $fileInfo, string $realPath, int $index) {
        $this->fileName = $fileInfo->getBasename();
        $this->absolutePath = $realPath;
        $this->index = $index;
        $this->isDir = $fileInfo->isDir();
        $this->ext = $this->isDir ? "" : $fileInfo->getExtension();
        $this->size = $this->isDir ? -1 : $this->formatBytes($fileInfo->getSize());
        $this->perms = substr(sprintf('%o', $fileInfo->getPerms()), -3);
        $this->user = $fileInfo->getOwner();
        $this->group = $fileInfo->getGroup();
        $readableBitSet = $this->permissionCheck(2);
        $writeableBitSet = $this->permissionCheck(1);
        $executableBitSet = $this->permissionCheck(0);
        $this->isReadable = $this->isDir ? $executableBitSet : $readableBitSet;
        $this->isWriteable = $this->isDir ? $executableBitSet && $writeableBitSet : $writeableBitSet;
        $this->isExecutable = $executableBitSet;
        $this->infoObject = $fileInfo;
    }
    /**
     * Checks wether or not a given bit is set on $this->perms
     *
     * @param  integer $position
     * @return bool
     */
    private function permissionCheck(int $position): bool {
        if(UserGroupCache::getExecutingUser() === 0) {
            return true;
        }
        else  if ($this->user === UserGroupCache::getExecutingUser() && $this->perms {0} & (1 << $position)) {
            return true;
        } else if (in_array($this->group, UserGroupCache::getExecutingGroups()) && $this->perms {1} & (1 << $position)) {
            return true;
        } else if ($this->perms {2} & (1 << $position)) {
            return true;
        }
        return false;
    }

    public function formatBytes(int $bytes): string {
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
    public $parentFolder;
    public $currentFolder;
    public $uidMap = [];
    public $gidMap = [];

    public function __construct(string $path, array $idList = []) {
        $getAll = count($idList) === 0;
        if (is_readable($path)) {
            $dir = new DirectoryIterator($path);
            if ($path !== "/") {
                $parentFolder = new SplFileInfo($path . "/..");
                $entry = new DirectoryEntry($parentFolder, $parentFolder->getRealPath(), -1);
                $this->parentFolder = $entry;
                $this->addEntryToUserGroupMap($entry);
                
            }

            $counter = 0;
            foreach ($dir as $fileInfo) {
                if ($getAll || array_search($counter, $idList) !== false) {
                    $realPath = $fileInfo->getRealPath();
                    if (!$fileInfo->isDot() && $realPath !== false) {
                        $entry = new DirectoryEntry($fileInfo, $realPath, $counter);
                        $this->entries[] = $entry;
                        $this->addEntryToUserGroupMap($entry);
                    }
                }
                $counter++;
            }
        }
        $this->currentFolder = realpath($path);
    }

    private function addEntryToUserGroupMap(DirectoryEntry $dirEntry) {
        if(!isset($this->uidMap[$dirEntry->user])) {
            $this->uidMap[$dirEntry->user] = UserGroupCache::resolveUser($dirEntry->user);
        }
        if(!isset($this->gidMap[$dirEntry->group])) {
            $this->gidMap[$dirEntry->group] = UserGroupCache::resolveUser($dirEntry->group);
        }
    }
}

class UserGroupCache {
    protected static $uid;
    protected static $gids;
    protected static $uidMap = [];
    protected static $gidMap = [];

    public static function resolveUser(int $uid): string {
        if (!isset(self::$uidMap[$uid])) {
            self::$uidMap[$uid] = posix_getpwuid($uid)["name"];
        }
        return self::$uidMap[$uid];
    }

    public static function resolveGroup(int $gid): string {
        if (!isset(self::$gidMap[$gid])) {
            self::$gidMap[$gid] = posix_getgrgid($gid)["name"];
        }
        return self::$gidMap[$gid];
    }

    public static function getExecutingUser(): int {
        if (!isset(self::$uid)) {
            self::$uid = posix_getuid();
        }
        return self::$uid;
    }

    public static function getExecutingGroups() : array {
        if (!isset(self::$gids)) {
            self::$gids = posix_getgroups();
        }
        return self::$gids;
    }
}
