<?php
setlocale(LC_ALL, "en_US.UTF-8");
class DirectoryEntry {
    public $fileName;
    public $ext;
    public $index;
    public $isDir;
    public $size;
    public $perms;
    public $isReadable;
    public $isWriteable;
    public $isExecutable;
    public $user;
    public $group;

    protected $absolutePath;

    public function __construct(SplFileInfo $fileInfo, string $realPath, int $index) {
        //public members, will be transmitted to webpage
        $this->fileName = $fileInfo->getBasename();
        $this->index = $index;
        $this->isDir = $fileInfo->isDir();
        $this->ext = $this->isDir ? "" : $fileInfo->getExtension();
        $this->size = $this->isDir ? -1 : $this->formatBytes($fileInfo->getSize());
        $this->perms = substr(sprintf('%o', $fileInfo->getPerms()), -3);
        $this->user = $fileInfo->getOwner();
        $this->group = $fileInfo->getGroup();
        $this->isReadable = UserGroupCache::isRoot() ? true : $fileInfo->isReadable();
        $this->isWriteable = UserGroupCache::isRoot() ? true : $fileInfo->isWritable();
        $this->isExecutable = $fileInfo->isExecutable();

        //protected members, internal to php only
        $this->absolutePath = $realPath;
    }

    private function formatBytes(int $bytes): string {
        $units = array("B", "KB", "MB", "GB", "TB");
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . " " . $units[$pow];
    }

    public function getAbsolutePath() {
        return $this->absolutePath;
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
            $this->gidMap[$dirEntry->group] = UserGroupCache::resolveGroup($dirEntry->group);
        }
    }
}

class UserGroupCache {
    protected static $uid;
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

    public static function isRoot() {
        if(!isset(self::$uid)) {
            self::$uid = posix_getuid();
        }
        return self::$uid === 0;
    }
}
