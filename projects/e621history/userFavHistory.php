<?php

require_once "util.php";
require_once "sql.php";
require_once "logger.php";
require_once "e621post.php";
require_once "e621user.php";

class UserfavHistory {
    private static $logfile = "userfavhistory.log";
    /**
     * @var PostParams
     */
    private $postParams;
    /**
     * Holds the users fav ids
     * @var string[]
     */
    private $favs;

    private $connection;

    public function __construct(PostParams $postParams) {
        $this->postParams = $postParams;
        $this->connection = SqlConnection::get("e621");
    }
    /**
     * Converts the object into json which can be interpreted by js graphing lib
     * @return string
     */
    public function generateGraph(): string {
        $result = new ResultJson($this->postParams->tagGroups, $this->postParams->providedLocalFiles);
        //TODO: logic cleanup
        $userFavCount = $this->getFavCount();
        //20229
        $maxFavsAtOnce = 1000;
        $indexStart = 0;
        $offset = $userFavCount - $maxFavsAtOnce;
        do {
            $jsonArray = $this->getFavsJson($maxFavsAtOnce, $offset, $indexStart);
            foreach ($jsonArray as $position => $json) {
                if ($this->postParams->providedLocalFiles && !isset($this->postParams->fileDates[$json->md5])) {
                    continue;
                }
                $post = new E621Post($json);
                $dataPoint = [];
                foreach ($this->postParams->tagGroups as $tagGroup) {
                    $matches = $post->tagsMatchesFilter($tagGroup);
                    $dataPoint[$tagGroup->groupName] = $matches;
                    if ($matches === true) {
                        continue;
                    }
                }
                $xAxis = $this->postParams->providedLocalFiles ? $this->postParams->fileDates[$post->md5] : $position + 1;
                $result->addDataPoint($xAxis, $dataPoint);
            }
            $offset -= $maxFavsAtOnce;
            $indexStart += $maxFavsAtOnce;
        } while (count($jsonArray) === $maxFavsAtOnce);
        $result->finalize();
        return json_encode($result);
    }
    /**
     * Returns $count user favs starting from $offset. Array will start counting from $indexStart
     *
     * @param  integer $count
     * @param  integer $offset
     * @param  integer $indexStart
     * @return array
     */
    private function getFavsJson(int $count, int $offset, int $indexStart): array{
        $end = $offset + $count - 1;
        $statement = $this->connection->prepare("SELECT json, position FROM posts JOIN user_favs ON posts.id = user_favs.id WHERE user_favs.user_id = :userid AND user_favs.position  BETWEEN {$offset} AND {$end} ORDER BY position ASC;");
        $statement->bindValue("userid", $this->postParams->userid);
        $statement->execute();
        $result = [];

        $counter = 0;
        while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            $json = json_decode(utf8_encode($row["json"]));
            $result[$indexStart + $counter] = $json;
            $counter++;
        }
        return $result;
    }

    /**
     * Returns the number of favs for the user in the db
     *
     * @return int
     */
    private function getFavCount(): int {
        $statement = $this->connection->prepare("SELECT COUNT(*) FROM user_favs WHERE user_id = :userid;");
        $statement->bindValue("userid", $this->postParams->userid);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_COLUMN);
    }
    /**
     * Writes all favs into the db and sets user status to finished when done
     *
     * @param  integer $userid
     * @param  string  $username
     * @return void
     */
    public static function populateDb(int $userid, string $username) {
        if (self::removeFromDb($userid) === false) {
            return;
        }
        $connection = SqlConnection::get("e621");
        $statementUserFav = $connection->prepare("INSERT INTO user_favs (user_id, id, position) VALUES (:userid, :id, :position)");

        $page = 1;
        $resultsPerPage = 320;
        $url = "https://e621.net/post/index.json?tags=fav:{$username}&limit={$resultsPerPage}&page=";
        $jsonArray = null;
        $counter = 0;
        $result = [];
        do {
            //api imposes a limit of 750 pages, which amounts to 240k posts
            if ($page > 750) {
                break;
            }
            $jsonArray = getJson($url . $page, ["user-agent" => "earlopain"]);
            $connection->beginTransaction();
            foreach ($jsonArray as $json) {
                $result[] = $json->id;
                $post = new E621Post($json);
                $post->save($connection);
                // save post as user fav with position
                $statementUserFav->bindValue("userid", $userid);
                $statementUserFav->bindValue("id", $json->id);
                $statementUserFav->bindValue("position", $counter);
                //Failed to insert because of key constraint
                if ($statementUserFav->execute() === false) {
                    $logger = Logger::get(self::$logfile);
                    $logger->log(LogLevel::ERROR, "Post insert failed for " . $username . " => " . $json->id);
                    $counter--;
                }
                $counter++;
            }
            $connection->commit();
            $page++;
        } while (count($jsonArray) === $resultsPerPage);

        $logger = Logger::get(self::$logfile);
        $statement = SqlConnection::get("e621")->prepare("INSERT INTO processed_users (user_id) VALUES (:userid)");
        $statement->bindValue("userid", $userid);
        if ($statement->execute() === true) {
            $logger->log(LogLevel::INFO, "Inserted {$counter} posts for user {$username}");
        } else {
            $logger->log(LogLevel::ERROR, "Failed to insert {$username} into db");
        }
    }

    /**
     * Erases users favs and the done status
     *
     * @param  integer   $userid
     * @return boolean
     */
    private static function removeFromDb(int $userid): bool {
        $connection = SqlConnection::get("e621");
        $statementRemoveUser = $connection->prepare("DELETE FROM processed_users WHERE user_id = :userid");
        $statementRemoveUser->bindValue("userid", $userid);
        $statementRemoveUserFavs = $connection->prepare("DELETE FROM user_favs WHERE user_id = :userid");
        $statementRemoveUserFavs->bindValue("userid", $userid);
        $result = $statementRemoveUser->execute() && $statementRemoveUserFavs->execute();
        if ($result === false) {
            $logger = Logger::get(self::$logfile);
            $logger->log(LogLevel::WARNING, "Failed to remove {$userid} from db");
        }
        return $result;
    }
    /**
     * Returns the number of favs for the user in the db
     *
     * @param  integer $userid
     * @return int
     */
    public static function countPostsInDb(int $userid): int {
        $statement = SqlConnection::get("e621")->prepare("SELECT COUNT(*) FROM user_favs WHERE user_id = :userid");
        $statement->bindValue("userid", $userid);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_COLUMN);
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
    private $sortByDate;
    private $dataPoints = [];
    public $xAxis = [];
    public $graphData = [];
    private $tagGroupCurrentValue = [];
    /**
     * @param TagGroup[] $tagGroups
     */
    public function __construct(array $tagGroups, bool $sortByDate) {
        $this->tagGroups = $tagGroups;
        $this->sortByDate = $sortByDate;
        foreach ($this->tagGroups as $tagGroup) {
            $this->graphData[$tagGroup->groupName] = [];
            $this->tagGroupCurrentValue[$tagGroup->groupName] = 0;
        }
    }

    public function addDataPoint($x, $dataPoint) {
        $this->dataPoints[$x] = $dataPoint;
    }

    public function finalize() {
        if ($this->sortByDate) {
            //sort favs by those transmitted mtimes
            uksort($this->dataPoints, function ($a, $b) {
                return $a - $b;
            });
        }
        foreach ($this->dataPoints as $x => $dataPoint) {
            $this->xAxis[] = $this->sortByDate ? date("Y-m-d", $x / 1000) : $x;
            foreach ($this->tagGroups as $tagGroup) {
                $this->tagGroupCurrentValue[$tagGroup->groupName] += $dataPoint[$tagGroup->groupName];
                $this->graphData[$tagGroup->groupName][] = $this->tagGroupCurrentValue[$tagGroup->groupName];
            }
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
        self::$regexCache[$input] = "/\\b{$regex}\\b/";
        return self::$regexCache[$input];
    }
}

class PostParams {
    /**
     * @var string
     */
    public $userid;
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
        $this->username = str_replace(" ", "_", trim($this->username));
        E621User::addToDb($this->username);
        $this->userid = E621User::usernameToId($this->username);
        if (!isset($json["tagGroups"])) {
            $json["tagGroups"] = [];
        }
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
