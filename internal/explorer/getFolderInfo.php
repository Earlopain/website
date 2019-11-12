<?php
setlocale(LC_ALL, "en_US.UTF-8");
class DirectoryEntry {
    /**
     * @var string
     */
    public $fileName;
    /**
     * @var string
     */
    public $absolutePath;
    /**
     * @var int
     */
    public $index;
    /**
     * @var bool
     */
    public $isDir;
    /**
     * @var int
     */
    public $size;
    /**
     * @var string
     */
    public $perms;
    /**
     * @var bool
     */
    public $isWriteable;
    /**
     * @var bool
     */
    public $isReadable;
    /**
     * @var bool
     */
    public $isExecutable;
    /**
     * @var string
     */
    public $user;
    /**
     * @var string
     */
    public $group;
    /**
     * @var SplFileInfo
     */
    public $infoObject;

    public function __construct(SplFileInfo $fileInfo, string $realPath, int $index, string $userName) {
        $this->fileName = $fileInfo->getBasename();
        $this->absolutePath = $realPath;
        $this->index = $index;
        $this->isDir = $fileInfo->isDir();
        $this->size = $this->isDir ? -1 : $this->formatBytes($fileInfo->getSize());
        $this->perms = substr(sprintf('%o', $fileInfo->getPerms()), -3);
        $this->user = UserGroupCache::resolveUser($fileInfo->getOwner());
        $this->group = UserGroupCache::resolveGroup($fileInfo->getGroup());
        $this->isReadable = $this->permissionCheck(2, $userName);
        $this->isWriteable = $this->permissionCheck(1, $userName);
        $this->isExecutable = $this->permissionCheck(0, $userName);
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
    /**
     * @var DirectoryEntry[]
     */
    public $entries = [];
    /**
     * @var integer
     */
    public $entriesCount = 0;
    /**
     * @var string
     */
    public $currentFolder;

    public function __construct(string $path, int $uid, array $idList = []) {
        $userContext = posix_getpwuid($uid)["name"];
        posix_setuid($uid);
        $getAll = count($idList) === 0;
        if (is_readable($path)) {
            $dir = new DirectoryIterator($path);
            $counter = 0;
            if ($path !== "/") {
                $parentFolder = new SplFileInfo($path . "/..");
                $this->entries[] = new DirectoryEntry($parentFolder, $parentFolder->getRealPath(), $counter++, $userContext);
            }

            foreach ($dir as $fileInfo) {
                if ($getAll || array_search($counter, $idList) !== false) {
                    $realPath = $fileInfo->getRealPath();
                    if (!$fileInfo->isDot() && $realPath !== false) {
                        $this->entries[] = new DirectoryEntry($fileInfo, $realPath, $counter, $userContext);
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
    /**
     * @var array
     */
    protected static $userCache = [];
    /**
     * @var array
     */
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
