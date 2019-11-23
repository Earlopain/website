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
    public $userString;
    public $group;
    public $groupString;
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
        $this->userString = UserGroupCache::resolveUser($this->user);
        $this->group = $fileInfo->getGroup();
        $this->groupString = UserGroupCache::resolveGroup($this->group);
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
        if(UserGroupCache::getUser() === 0) {
            return true;
        }
        else  if ($this->user === UserGroupCache::getUser() && $this->perms {0} & (1 << $position)) {
            return true;
        } else if (in_array($this->group, UserGroupCache::getGroups()) && $this->perms {1} & (1 << $position)) {
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
    public $entriesCount = 0;
    public $currentFolder;

    public function __construct(string $path, array $idList = []) {
        $getAll = count($idList) === 0;
        if (is_readable($path)) {
            $dir = new DirectoryIterator($path);
            if ($path !== "/") {
                $parentFolder = new SplFileInfo($path . "/..");
                $this->parentFolder = new DirectoryEntry($parentFolder, $parentFolder->getRealPath(), -1);
            }

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

    public static function getUser(): int {
        if (!isset(self::$uid)) {
            self::$uid = posix_getuid();
        }
        return self::$uid;
    }

    public static function getGroups() : array {
        if (!isset(self::$gids)) {
            self::$gids = posix_getgroups();
        }
        return self::$gids;
    }
}
