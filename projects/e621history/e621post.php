<?php

require_once "userFavHistory.php";
require_once "logger.php";
require_once "util.php";

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
        if ($this->isInDb($connection)) {
            if ($this->json->status === "deleted") {
                $statement = $connection->prepare("UPDATE posts SET status = :status, last_updated = NOW() WHERE id = :id;");
            } else {
                $statement = $connection->prepare("UPDATE posts SET json = :json, status = :status, last_updated = NOW() WHERE id = :id");
                $statement->bindValue("json", json_encode($this->json));
            }
        } else {
            $statement = $connection->prepare("INSERT INTO posts (id, md5, json, status, last_updated) VALUES (:id, :md5, :json, :status, NOW())");
            if (isset($this->md5)) {
                $statement->bindValue("md5", $this->md5);
            } else {
                $statement->bindValue("md5", null);
            }
            $statement->bindValue("json", json_encode($this->json));
        }
        $statement->bindValue("id", $this->id);
        $statement->bindValue("status", $this->json->status);

        $statement->execute();
    }

    public static function saveNuked(PDO $connection, int $id) {
        $statement = $connection->prepare("INSERT INTO posts (id, status, last_updated) VALUES (:id, :status, NOW())");
        $statement->bindValue("id", $id);
        $statement->bindValue("status", "nuked");
        $statement->execute();
    }

    /**
     * Saves the posts file as a blob in the db
     *
     * @param  PDO $connection
     * @return int Returns one of the post_file constants
     */
    public function saveFile(PDO $connection): int {
        if ($this->hasFile($connection)) {
            return POST_FILE_ALREADY_DOWNLOADED;
        } else if ($this->json->status === "deleted") {
            return POST_FILE_DELETED;
        }
        $fileContent = file_get_contents($this->json->file_url);
        if (strlen($fileContent) !== $this->json->file_size) {
            Logger::log("mirror.log", LOG_ERR, "Network error for {$this->md5}");
            return POST_FILE_RETRY;
        }

        $fp = fopen("php://memory", "r+");
        fputs($fp, $fileContent);
        rewind($fp);
        $statement = $connection->prepare("UPDATE posts SET file = :fp WHERE id = :id");
        $statement->bindValue("id", $this->id);
        $statement->bindValue("fp", $fp, PDO::PARAM_LOB);
        $result = $statement->execute();
        if ($result === false) {
            Logger::log("mirror.log", LOG_CRIT, "Failed to insert {$this->md5}");
            die("FATAL ERROR");
        }
        return POST_FILE_SUCCESS;
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
