<?php

require_once "userFavHistory.php";
require_once "logger.php";

class E621Post {
    public $tags;
    public $md5;
    public $id;
    private $json;

    public function __construct($json) {
        $this->tags = $json->tags;
        $this->md5 = isset($json->md5) ? $json->md5 : null;
        $this->id = $json->id;
        $this->json = $json;
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
    public function save(PDO $connection) {
        if ($this->isInDb($connection) && $this->json->status === "deleted") {
            $statement = $connection->prepare("INSERT INTO posts (id, md5, json, last_updated) VALUES (:id, :md5, :json, NOW())
                                            ON DUPLICATE KEY UPDATE last_updated = NOW();");
        } else {
            $statement = $connection->prepare("INSERT INTO posts (id, md5, json, last_updated) VALUES (:id, :md5, :json, NOW())
                                            ON DUPLICATE KEY UPDATE json = :json, last_updated = NOW();");
        }
        $statement->bindValue("id", $this->id);
        if (isset($this->md5)) {
            $statement->bindValue("md5", $this->md5);
        } else {
            $statement->bindValue("md5", null);
        }
        $statement->bindValue("json", json_encode($this->json));
        $statement->execute();
    }

    /**
     * Saves the posts file as a blob in the db
     *
     * @param  PDO     $connection
     * @return boolean True on success, false on failure
     */
    public function saveFile(PDO $connection): bool {
        if ($this->hasFile($connection)) {
            return false;
        }
        $fp = fopen($this->json->file_url, "r");
        if ($fp === false) {
            return false;
        }
        $statement = $connection->prepare("UPDATE posts SET file = :fp WHERE id = :id");
        $statement->bindValue("id", $this->id);
        $statement->bindValue("fp", $fp, PDO::PARAM_LOB);
        $result = $statement->execute();
        if ($result === false) {
            $logger = Logger::get("mirror.log");
            $logger->log(LogLevel::ERROR, "Failed to insert {$this->md5}");
        }
        return $result;
    }
    /**
     * Checks wether or not the file is already in the db
     *
     * @param  PDO       $connection
     * @return boolean
     */
    public function hasFile(PDO $connection): bool {
        $statement = $connection->prepare("SELECT 1 FROM posts WHERE id = :id AND file IS NOT NULL");
        $statement->bindValue("id", $this->id);
        $statement->execute();
        return $statement->fetch() !== false;
    }

    public function isInDb(PDO $connection) {
        $statement = $connection->prepare("SELECT 1 FROM posts WHERE id = :id");
        $statement->bindValue("id", $this->id);
        $statement->execute();
        return $statement->fetch() !== false;
    }
}
