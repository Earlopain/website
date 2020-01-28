<?php

class UserfavHistory {
    private static $userfavsFolder = __DIR__ . "/e621userfavs";
    private static $postJsonFolder = __DIR__ . "/e621posts";

    private $username;
    private $tagGroups;
    private $favs;

    public function __construct($username, $tagGroups) {
        createDirIfNotExists(self::$userfavsFolder);
        createDirIfNotExists(self::$postJsonFolder);
        $this->username = $username;
        $this->tagGroups = $tagGroups;
    }

    public function generateGraph() {
        $postMatches = [];
        $userfavs = array_reverse($this->getAllFavs());
        foreach ($userfavs as $userfavMd5) {
            $postMatches[$userfavMd5] = [];
            $userfavJson = json_decode(file_get_contents(self::$postJsonFolder . "/" . $userfavMd5 . ".json"));
            foreach ($this->tagGroups as $tagGroupKey => $dummy) {
                foreach ($this->tagGroups[$tagGroupKey] as $filter) {
                    $matches = tagsMatchesFilter($userfavJson->tags, $filter);
                    $postMatches[$userfavMd5][$tagGroupKey] = $matches;
                    if ($matches === true) {
                        break;
                    }
                }
            }
        }
        $csvHeader = "date";
        $tagCounter = [];
        foreach (array_keys($this->tagGroups) as $key) {
            $tagCounter[$key] = 0;
            $csvHeader .= ";" . $key;
        }

        $filestat = getAllStat();
        $sortKeys = array_filter(array_keys($postMatches), function ($a) use (&$filestat) {
            return isset($filestat[$a]);
        });
        uasort($sortKeys, function ($a, $b) use (&$filestat) {
            return $filestat[$a] - $filestat[$b];
        });
        $csv = "";
        foreach ($sortKeys as $md5) {
            if (!isset($filestat[$md5])) {
                continue;
            }
            $csv .= "\n" . date("Y-m-d", $filestat[$md5]);
            foreach (array_keys($this->tagGroups) as $key) {
                $tagCounter[$key] += $postMatches[$md5][$key];
                $csv .= ";" . $tagCounter[$key];
            }
        }
        return $csvHeader . $csv;
    }

    private function getAllFavs() {
        if (isset($this->favs)) {
            return $this->favs;
        }
        $userfavPath = self::$userfavsFolder . "/" . $this->username . ".json";
        if (file_exists($userfavPath)) {
            return json_decode(file_get_contents($userfavPath));
        }
        $page = 1;
        $resultsPerPage = 320;
        $url = "https://e621.net/post/index.json?tags=fav:" . $this->username . "&limit=" . $resultsPerPage . "&page=";
        $jsonArray = null;
        $favMd5 = [];
        do {
            if ($page > 750) {
                break;
            }
            $jsonArray = getJSON($url . $page);
            foreach ($jsonArray as $json) {
                $favMd5[] = $json->md5;
                savePost($json);
            }
            $page++;
        } while (count($jsonArray) === $resultsPerPage);
        file_put_contents($userfavPath, json_encode($favMd5));
        $this->favs = $favMd5;
        return $favMd5;
    }
}

function tagsMatchesFilter($tagString, $filterString) {
    $seperatedFilters = explode(" ", $filterString);
    $result = true;

    foreach ($seperatedFilters as $filter) {
        $inverse = $filter{0} === "-";
        $filterNoMinus = $inverse ? substr($filter, 1) : $filter;
        $regex = RegexCache::escapeStringToRegex($filterNoMinus);
        $result = preg_match($regex, $tagString) === 1 ? true : false;
        $result = $result !== $inverse;
        if ($result === false) {
            break;
        }
    }
    return $result;
}

function savePost($json) {
    global $postJsonFolder;
    $filepath = $postJsonFolder . "/" . $json->md5 . ".json";
    if (file_exists($filepath)) {
        return;
    }
    file_put_contents($filepath, json_encode($json));
}

function getJSON($url) {
    $context = stream_context_create(["http" => ["user_agent" => "earlopain"]]);
    $result = file_get_contents($url, false, $context);
    return json_decode($result);
}

function createDirIfNotExists($path) {
    if (!file_exists($path)) {
        $result = mkdir($path);
        if ($result === false) {
            throw new Error("Failed to create " . $path);
        }
    }
}

function getAllStat() {
    $glob = "/media/plex/plexmedia/e621/**/**";
    $result = [];
    foreach (glob($glob) as $file) {
        $md5 = pathinfo($file, PATHINFO_FILENAME);
        $fp = fopen($file, "r");
        $stat = fstat($fp);
        if (isset($stat["mtime"])) {
            $result[$md5] = $stat["mtime"];
        }
    }
    return $result;
}

class RegexCache {
    static $regexCache = [];
    public static function escapeStringToRegex($string) {
        if (isset(self::$regexCache[$string])) {
            return self::$regexCache[$string];
        }
        $regex = preg_quote($string, "/");
        $regex = str_replace("\\*", ".*?", $regex);
        self::$regexCache[$string] = "/\\b" . $regex . "\\b/";
        return self::$regexCache[$string];
    }
}

class PostParams {
    public $username;
    public $tagGroups;
    public function __construct($jsonString) {
        $json = json_decode($jsonString, true);
        $this->username = $json["username"];
        $this->tagGroups = $json["tagGroups"];
    }

    public static function create() {
        return new self(file_get_contents("php://input"));
    }
}
$postParams = PostParams::create();
$favs = new UserfavHistory($postParams->username, $postParams->tagGroups);
echo $favs->generateGraph();
