<?php

$userfavsFolder = __DIR__ . "/e621userfavs";
$postJsonFolder = __DIR__ . "/e621posts";
createDirIfNotExists($userfavsFolder);
createDirIfNotExists($postJsonFolder);

function tagsMatchesFilter($tagString, $filterString) {
    $seperatedFilters = explode(" ", $filterString);
    $allTags = explode(" ", $tagString);
    $result = true;

    foreach ($seperatedFilters as $filter) {
        $inverse = $filter{0} === "-";
        $filterNoMinus = $inverse ? substr($filter, 1) : $filter;
        if ($result === false) {
            break;
        }
        if (strpos($filterNoMinus, "*") !== false) {
            $regex = RegexCache::escapeStringToRegex($filterNoMinus);
            $result = preg_match($regex, $tagString) === 1 ? true : false;
        } else {
            //if there is no wildcard, the filter and tag must match
            $matchFound = false;
            foreach ($allTags as $tag) {
                if ($tag === $filterNoMinus) {
                    $matchFound = true;
                    break;
                }
            }
            $result = $matchFound;
        }
        $result = $result !== $inverse;
    }
    return $result;
}

function getAllUserFavs($username) {
    global $userfavsFolder;
    $userfavPath = $userfavsFolder . "/" . $username . ".json";
    if (file_exists($userfavPath)) {
        return json_decode(file_get_contents($userfavPath));
    }
    $page = 1;
    $resultsPerPage = 320;
    $url = "https://e621.net/post/index.json?tags=fav:" . $username . "&limit=" . $resultsPerPage . "&page=";
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
    return $favMd5;
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

$username = "earlopain";
$tagGroups = [
    "gay" => ["male/male -bisexual -male/female", "male solo -bisexual"],
    "straight" => ["male/female -bisexual", "female solo"]
];

$postMatches = [];
$userfavs = array_reverse(getAllUserFavs($username));
foreach ($userfavs as $userfavMd5) {
    $postMatches[$userfavMd5] = [];
    $userfavJson = json_decode(file_get_contents($postJsonFolder . "/" . $userfavMd5 . ".json"));
    foreach ($tagGroups as $tagGroupKey => $dummy) {
        foreach ($tagGroups[$tagGroupKey] as $filter) {
            $matches = tagsMatchesFilter($userfavJson->tags, $filter);
            $postMatches[$userfavMd5][$tagGroupKey] = $matches;
            if ($matches === true) {
                break;
            }
        }
    }
}

$csv = "date;straight;gay";
$currentStraigt = 0;
$currentGay = 0;

$filestat = getAllStat();

$sortKeys = array_keys($postMatches);
$sortKeys = array_filter($sortKeys, function ($a) {
    global $filestat;
    return isset($filestat[$a]);
});
uasort($sortKeys, function ($a, $b) {
    global $filestat;
    return $filestat[$a] - $filestat[$b];
});

foreach ($sortKeys as $md5) {
    if (!isset($filestat[$md5])) {
        continue;
    }
    $currentStraigt += $postMatches[$md5]["straight"];
    $currentGay += $postMatches[$md5]["gay"];
    $csv .= "\n" . date("Y-m-d", $filestat[$md5]) . ";" . $currentStraigt . ";" . $currentGay;
}
echo $csv;

function getAllStat() {
    $glob = "/media/plex/plexmedia/e621/**/**";
    $result = [];
    foreach (glob($glob) as $file) {
        $md5 = pathinfo($file, PATHINFO_FILENAME);
        $fp = fopen($file, "r");
        $stat = fstat($fp);
        if (isset($stat["mtime"]) && $stat["mtime"] > 1420070400) {
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
        $regex = str_replace("\\*", "[\\s\\S]*?", $regex);
        self::$regexCache[$string] = "/\\b" . $regex . "\\b/";
        return self::$regexCache[$string];
    }
}
