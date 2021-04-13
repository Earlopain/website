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
        $this->tags = "";
        $tagArray = [];
        foreach ($json->tags as $key => $value) {
            $tagArray = array_merge($tagArray, $value);
        }
        $this->tags = implode(" ", $tagArray);
        $this->md5 = isset($json->file->md5) ? $json->file->md5 : null;
        if ($this->md5 === null || strlen($this->md5) !== 32) {
            die("MD5 FAIL");
        }
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
                $inverse = strpos($filter, "-") === 0;
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
     * version will be overwritten
     * @return void
     */
    public function save(PDO $connection) {
        $statement = $connection->prepare("INSERT INTO posts (id, md5, json, last_updated) VALUES (:id, :md5, :json, NOW())
                                                ON DUPLICATE KEY UPDATE md5 = :md5, json = :json, last_updated = NOW()");
        $statement->bindValue("id", $this->id);
        $statement->bindValue("md5", $this->md5);
        $statement->bindValue("json", json_encode($this->json));
        $statement->execute();
    }

    public static function saveNuked(PDO $connection, int $id) {
        $statement = $connection->prepare("INSERT INTO posts (id, last_updated) VALUES (:id, NOW())");
        $statement->bindValue("id", $id);
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
        } else if ($this->json->flags->deleted) {
            return POST_FILE_DELETED;
        }
        //Investigate
        $a = substr($this->md5, 0, 2);
        $b = substr($this->md5, 2, 2);
        $fileContent = file_get_contents("https://static1.e621.net/data/{$a}/{$b}/{$this->md5}.{$this->json->file->ext}");
        if (strlen($fileContent) !== $this->json->file->size) {
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
            echo ("Failed to insert {$this->md5}");
            return POST_FILE_RETRY;
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
