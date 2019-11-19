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

    public function __construct(SplFileInfo $fileInfo, string $realPath, int $index, string $userName) {
        $this->fileName = $fileInfo->getBasename();
        $this->absolutePath = $realPath;
        $this->index = $index;
        $this->isDir = $fileInfo->isDir();
        $this->ext = $this->isDir ? "" : $fileInfo->getExtension();
        $this->size = $this->isDir ? -1 : $this->formatBytes($fileInfo->getSize());
        $this->perms = substr(sprintf('%o', $fileInfo->getPerms()), -3);
        $this->user = UserGroupCache::resolveUser($fileInfo->getOwner());
        $this->group = UserGroupCache::resolveGroup($fileInfo->getGroup());
        $readableBitSet = $this->permissionCheck(2, $userName);
        $writeableBitSet = $this->permissionCheck(1, $userName);
        $executableBitSet = $this->permissionCheck(0, $userName);
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
    private function permissionCheck(int $position, string $userName): bool {
        if ($this->user === $userName && $this->perms {0} & (1 << $position)) {
            return true;
        } else if (in_array($this->group, UserGroupCache::getGroups($userName)) && $this->perms {1} & (1 << $position)) {
            return true;
        } else if ($this->perms {2} & (1 << $position)) {
            return true;
        }
        return false;
    }
    /**
     * Turns byte count into human readable format
     *
     * @param  integer  $bytes
     * @return string
     */
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

    public function __construct(int $uid, string $path, array $idList = []) {
        $userContext = posix_getpwuid($uid);
        $userName = $userContext["name"];
        posix_setgid($userContext["gid"]);
        posix_initgroups($userContext["name"], $userContext["gid"]);
        posix_setuid($uid);
        $getAll = count($idList) === 0;
        if (is_readable($path)) {
            $dir = new DirectoryIterator($path);
            if ($path !== "/") {
                $parentFolder = new SplFileInfo($path . "/..");
                $this->parentFolder = new DirectoryEntry($parentFolder, $parentFolder->getRealPath(), -1, $userName);
            }

            $counter = 0;
            foreach ($dir as $fileInfo) {
                if ($getAll || array_search($counter, $idList) !== false) {
                    $realPath = $fileInfo->getRealPath();
                    if (!$fileInfo->isDot() && $realPath !== false) {
                        $this->entries[] = new DirectoryEntry($fileInfo, $realPath, $counter, $userName);
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
    protected static $groups = [];

    /**
     * Converts user id into user string
     *
     * @param  int      $uid
     * @return string
     */
    public static function resolveUser(int $uid): string {
        if (!isset(self::$userCache[$uid])) {
            self::$userCache[$uid] = posix_getpwuid($uid)["name"];
        }
        return self::$userCache[$uid];
    }
    /**
     * Converts group id into group string
     *
     * @param  integer  $gid
     * @return string
     */
    public static function resolveGroup(int $gid): string {
        if (!isset(self::$groupCache[$gid])) {
            self::$groupCache[$gid] = posix_getgrgid($gid)["name"];
        }
        return self::$groupCache[$gid];
    }
    /**
     * Gets the groups the current user is a member of
     *
     * @return string[]
     */
    public static function getGroups(string $userName) {
        if (!isset(self::$groups[$userName])) {
            $groups = substr(exec("groups {$userName} | cut -d':' -f2"), 1);
            self::$groups[$userName] = explode(" ", $groups);
        }
        return self::$groups[$userName];
    }
}
