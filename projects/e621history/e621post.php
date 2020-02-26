<?php

require_once "userFavHistory.php";

class E621Post {
    private $tags;

    private static $saveStatementSql = "INSERT INTO posts (md5, json, last_updated) VALUES (:md5, :json, NOW())
                                        ON DUPLICATE KEY UPDATE json = :json, last_updated = NOW();";
    /**
     * @var PDOStatement
     */
    private static $saveStatement;
    public function __construct($json) {
        $this->tags = $json->tags;
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
                $result = preg_match($regex, $this->tags) === 1 ? true : false;
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
     * Saves the post to the db. If is already exists, the current
     * version will be overwritten and last_update gets set to now()
     * @return void
     */
    public static function savePost(PDO $connection, $json) {
        $statement = self::getSaveStatement($connection);
        $statement->bindValue("md5", $json->md5);
        $statement->bindValue("json", json_encode($json));
        $statement->execute();
    }

    public static function getSaveStatement(PDO $connection): PDOStatement {
        if (!isset(self::$saveStatement)) {
            self::$saveStatement = $connection->prepare(self::$saveStatementSql);
        }
        return self::$saveStatement;
    }
}
