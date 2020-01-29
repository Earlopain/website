<?php

require_once "util.php";

class UserfavHistory {
    private static $userfavsFolder = __DIR__ . "/e621userfavs";

    private $postParams;
    private $favs;

    public function __construct($postParams) {
        createDirIfNotExists(self::$userfavsFolder);
        $this->postParams = $postParams;
    }

    public function generateGraph() {
        $postMatches = [];
        $userfavs = array_reverse($this->getAllFavs());
        foreach ($userfavs as $userfavMd5) {
            $postMatches[$userfavMd5] = [];
            $userfavJson = E621Post::createFromMd5($userfavMd5);
            foreach (array_keys($this->postParams->tagGroups) as $tagGroupKey) {
                foreach ($this->postParams->tagGroups[$tagGroupKey] as $filter) {
                    $matches = $userfavJson->tagsMatchesFilter($filter);
                    $postMatches[$userfavMd5][$tagGroupKey] = $matches;
                    if ($matches === true) {
                        break;
                    }
                }
            }
        }
        $result = new ResultJson();
        foreach (array_keys($this->postParams->tagGroups) as $key) {
            $result->addGroup($key);
        }
        for ($i = 0; $i < count($userfavs); $i++) {
            $result->addDataPoint($i, $postMatches[$userfavs[$i]]);
        }
        return json_encode($result);
    }

    private function getAllFavs() {
        if (isset($this->favs)) {
            return $this->favs;
        }
        $userfavPath = self::$userfavsFolder . "/" . $this->postParams->username . ".json";
        if (file_exists($userfavPath)) {
            return json_decode(file_get_contents($userfavPath));
        }
        $page = 1;
        $resultsPerPage = 320;
        $url = "https://e621.net/post/index.json?tags=fav:" . $this->postParams->username . "&limit=" . $resultsPerPage . "&page=";
        $jsonArray = null;
        $favMd5 = [];
        do {
            if ($page > 750) {
                break;
            }
            $jsonArray = getJson($url . $page, ["user-agent" => "earlopain"]);
            foreach ($jsonArray as $json) {
                $favMd5[] = $json->md5;
                $post = new E621Post($json);
                $post->savePost();
            }
            $page++;
        } while (count($jsonArray) === $resultsPerPage);
        file_put_contents($userfavPath, json_encode($favMd5));
        $this->favs = $favMd5;
        return $favMd5;
    }
}

class ResultJson {
    public $tagGroups = [];
    public $xAxis = [];
    private $tagGroupCurrentValue = [];

    public function addGroup($groupName) {
        $this->tagGroups[$groupName] = [];
        $this->tagGroupCurrentValue[$groupName] = 0;
    }

    public function addDataPoint($x, $data) {
        $this->xAxis[] = $x;
        foreach (array_keys($this->tagGroups) as $groupName) {
            $this->tagGroupCurrentValue[$groupName] += $data[$groupName];
            $this->tagGroups[$groupName][] = $this->tagGroupCurrentValue[$groupName];
        }
    }
}

class E621Post {
    private static $postJsonFolder = __DIR__ . "/e621posts";
    private $json;

    public function __construct($jsonObject) {
        $this->json = $jsonObject;
    }

    public function tagsMatchesFilter($filterString) {
        $seperatedFilters = explode(" ", $filterString);
        $result = true;

        foreach ($seperatedFilters as $filter) {
            $inverse = $filter{0} === "-";
            $filterNoMinus = $inverse ? substr($filter, 1) : $filter;
            $regex = RegexCache::escapeStringToRegex($filterNoMinus);
            $result = preg_match($regex, $this->json->tags) === 1 ? true : false;
            $result = $result !== $inverse;
            if ($result === false) {
                break;
            }
        }
        return $result;
    }

    public function savePost() {
        $filepath = self::$postJsonFolder . "/" . $this->json->md5 . ".json";
        if (file_exists($filepath)) {
            return;
        }
        createDirIfNotExists(self::$postJsonFolder);
        file_put_contents($filepath, json_encode($this->json));
    }

    public static function createFromMd5($md5) {
        return new self(json_decode(file_get_contents(self::$postJsonFolder . "/" . $md5 . ".json")));
    }
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
    public $fileDates;
    public $providedLocalFiles;
    public function __construct($jsonString) {
        $json = json_decode($jsonString, true);
        $this->username = strtolower($json["username"]);
        $this->tagGroups = $json["tagGroups"];
        $this->fileDates = $json["fileDates"];
        $this->providedLocalFiles = count($this->fileDates) > 0;
    }

    public static function create() {
        return new self(file_get_contents("php://input"));
    }
}
$postParams = PostParams::create();
$favs = new UserfavHistory($postParams);
echo $favs->generateGraph();
