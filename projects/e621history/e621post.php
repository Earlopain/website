<?php

require_once "userFavHistory.php";

class E621Post {
    public $tags;
    public $md5;
    public $id;

    public function __construct($json) {
        $this->tags = $json->tags;
        $this->md5 = isset($json->md5) ? $json->md5 : null;
        $this->id = $json->id;
    }
    /**
     * Checks wether or not a given filter matches the post or not
     * @param  TagGroup $tagGroup
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
     * version will be overwritten IF the status is not deleted and last_update gets set to now()
     * @return void
     */
    public static function savePost(PDO $connection, $json) {
        if (self::postIsInDb($connection, $json->id) && $json->status === "deleted") {
            $statement = $connection->prepare("INSERT INTO posts (id, md5, json, last_updated) VALUES (:id, :md5, :json, NOW())
                                            ON DUPLICATE KEY UPDATE last_updated = NOW();");
        } else {
            $statement = $connection->prepare("INSERT INTO posts (id, md5, json, last_updated) VALUES (:id, :md5, :json, NOW())
                                            ON DUPLICATE KEY UPDATE json = :json, last_updated = NOW();");
        }
        $statement->bindValue("id", $json->id);
        if (isset($json->md5)) {
            $statement->bindValue("md5", $json->md5);
        } else {
            $statement->bindValue("md5", null);
        }
        $statement->bindValue("json", json_encode($json));
        $statement->execute();
    }

    public static function postIsInDb(PDO $connection, int $id) {
        $statement = $connection->prepare("SELECT 1 FROM posts WHERE id = :id");
        $statement->bindValue("id", $id);
        $statement->execute();
        return $statement->fetch() !== false;
    }
}
