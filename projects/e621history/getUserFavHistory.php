<?php

require_once "util.php";

class UserfavHistory {
    private static $userfavsFolder = __DIR__ . "/e621userfavs";
    /**
     * @var PostParams
     */
    private $postParams;
    /**
     * Holds the users fav md5
     * @var string[]
     */
    private $favs;

    public function __construct(PostParams $postParams) {
        createDirIfNotExists(self::$userfavsFolder);
        $this->postParams = $postParams;
    }
    /**
     * Converts the object into json which can be interpreted by js graphing lib
     * @return string
     */
    public function generateGraph(): string {
        $result = new ResultJson($this->postParams->tagGroups);
        $userfavs = $this->getAllFavs();

        if ($this->postParams->providedLocalFiles) {
            //remove files which are not found in post data
            $userfavs = array_filter($userfavs, function ($a) {
                return isset($this->postParams->fileDates[$a]);
            });
            //sort favs by those transmitted mtimes
            usort($userfavs, function ($a, $b) {
                return $this->postParams->fileDates[$a] - $this->postParams->fileDates[$b];
            });
        } else {
            //because we walk our way backwards through the favs reverse the array
            $userfavs = array_reverse($userfavs);
        }

        foreach ($userfavs as $index => $userfavMd5) {
            $userfavJson = E621Post::createFromMd5($userfavMd5);
            $dataPoint = [];
            foreach ($this->postParams->tagGroups as $tagGroup) {
                $matches = $userfavJson->tagsMatchesFilter($tagGroup);
                $dataPoint[$tagGroup->groupName] = $matches;
                if ($matches === true) {
                    continue;
                }
            }
            $xAxis = $this->postParams->providedLocalFiles ? date("Y-m-d", $this->postParams->fileDates[$userfavMd5] / 1000) : $index;
            $result->addDataPoint($xAxis, $dataPoint);
        }
        return json_encode($result);
    }
    /**
     * Populates and returns the users fav md5
     * @return string[]
     */
    private function getAllFavs(): array{
        if (isset($this->favs)) {
            return $this->favs;
        }
        $userfavPath = self::$userfavsFolder . "/" . $this->postParams->username . ".json";
        if (file_exists($userfavPath) && !$this->postParams->refreshUserFavs) {
            return json_decode(file_get_contents($userfavPath));
        }
        $page = 1;
        $resultsPerPage = 320;
        $url = "https://e621.net/post/index.json?tags=fav:" . $this->postParams->username . "&limit=" . $resultsPerPage . "&page=";
        $jsonArray = null;
        $favMd5 = [];
        do {
            //api imposes a limit of 750 pages, which amounts to 240k posts
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

class E621Post {
    /**
     * @var string
     */
    private static $postJsonFolder = __DIR__ . "/e621posts";
    private $json;

    public function __construct($jsonObject) {
        $this->json = $jsonObject;
    }
    /**
     * Checks wether or not a given filter matches the post or not
     * @param  string $filterString
     * @return bool
     */
    public function tagsMatchesFilter(TagGroup $tagGroup): bool {
        $result = false;
        foreach ($tagGroup->allFilters as $seperatedFilters) {
            foreach ($seperatedFilters as $filter) {
                //if filter starts with '-' the opposite should match
                $inverse = $filter{0} === "-";
                $filterNoMinus = $inverse ? substr($filter, 1) : $filter;
                $regex = RegexCache::escapeStringToRegex($filterNoMinus);
                $result = preg_match($regex, $this->json->tags) === 1 ? true : false;
                $result = $result !== $inverse;
                if ($result === false) {
                    break;
                }
            }
            if ($result === true) {
                break;
            }
        }

        return $result;
    }
    /**
     * Saves the post to the file system. If is already exists, nothing happens
     * @return void
     */
    public function savePost() {
        $filepath = self::$postJsonFolder . "/" . $this->json->md5 . ".json";
        if (file_exists($filepath)) {
            return;
        }
        createDirIfNotExists(self::$postJsonFolder);
        file_put_contents($filepath, json_encode($this->json));
    }
    /**
     * Creates a self instance from the given md5
     * Tries to only read from file system. If it doesn't exist it will error
     *
     * @param  string $md5
     * @return self
     */
    public static function createFromMd5(string $md5): self {
        return new self(json_decode(file_get_contents(self::$postJsonFolder . "/" . $md5 . ".json")));
    }
}

class TagGroup {
    /**
     * @var string
     */
    public $groupName;
    /**
     * @var array[]
     */
    public $allFilters = [];

    public function __construct(string $groupName, array $allFilters) {
        $this->groupName = $groupName;
        foreach ($allFilters as $value) {
            $this->allFilters[] = explode(" ", $value);
        }
    }
}

class ResultJson {
    /**
     * @var TagGroup[]
     */
    private $tagGroups;
    public $xAxis = [];
    public $graphData = [];
    private $tagGroupCurrentValue = [];
    /**
     * @param TagGroup[] $tagGroups
     */
    public function __construct(array $tagGroups) {
        $this->tagGroups = $tagGroups;
        foreach ($this->tagGroups as $tagGroup) {
            $this->graphData[$tagGroup->groupName] = [];
            $this->tagGroupCurrentValue[$tagGroup->groupName] = 0;
        }
    }

    public function addDataPoint($x, $dataPoint) {
        $this->xAxis[] = $x;
        foreach ($this->tagGroups as $tagGroup) {
            $this->tagGroupCurrentValue[$tagGroup->groupName] += $dataPoint[$tagGroup->groupName];
            $this->graphData[$tagGroup->groupName][] = $this->tagGroupCurrentValue[$tagGroup->groupName];
        }
    }
}

class RegexCache {
    static $regexCache = [];
    /**
     * Converts e621 style tag (with * matching) to a regex usable by php
     * @param  string   $string
     * @return string
     */
    public static function escapeStringToRegex(string $input): string {
        if (isset(self::$regexCache[$input])) {
            return self::$regexCache[$input];
        }
        $regex = preg_quote($input, "/");
        $regex = str_replace("\\*", ".*?", $regex);
        self::$regexCache[$input] = "/\\b" . $regex . "\\b/";
        return self::$regexCache[$input];
    }
}

class PostParams {
    /**
     * @var string
     */
    public $username;
    /**
     * @var TagGroup[]
     */
    public $tagGroups = [];
    /**
     * If selected from user will contain md5 => date
     * @var array
     */
    public $fileDates;
    /**
     * Wether or not the user selected folder to upload
     * @var bool
     */
    public $providedLocalFiles;
    /**
     * If true, will force a new download of user favs
     * @var bool
     */
    public $refreshUserFavs;
    public function __construct(string $jsonString) {
        $json = json_decode($jsonString, true);
        $this->username = strtolower($json["username"]);
        $this->username = str_replace(" ", "_", $this->username);
        foreach ($json["tagGroups"] as $key => $value) {
            $this->tagGroups[] = new TagGroup($key, $value);
        }
        $this->fileDates = $json["fileDates"];
        $this->providedLocalFiles = count($this->fileDates) > 0;
        $this->refreshUserFavs = $json["refreshUserFavs"];
    }
    /**
     * Creates a class instace with post data from php=>input
     *
     * @return self
     */
    public static function create(): self {
        return new self(file_get_contents("php://input"));
    }
}

$postParams = PostParams::create();
$favs = new UserfavHistory($postParams);
echo $favs->generateGraph();
