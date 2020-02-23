<?php

require_once "sql.php";
require_once "userFavHistory.php";
require_once "logger.php";

class E621UserQueue {
    private static $logfile = "queue.log";
    /**
     * Returns the queue index of the user
     * Will be -1 if not in queue
     *
     * @param  string  $username
     * @return integer queue index
     */
    public static function queuePosition(string $username): int {
        $statementPosition = SqlConnection::get("e621")->prepare("SELECT counter FROM user_queue where user_name = :user");
        $statementPosition->bindValue("user", $username);
        $statementPosition->execute();
        $position = $statementPosition->fetch(PDO::FETCH_COLUMN);
        //Not in queue
        if ($position === false) {
            $logger = Logger::get(self::$logfile);
            $logger->log(LogLevel::INFO, "User {$username} not in queue");
            return -1;
        }
        $statementMinCount = SqlConnection::get("e621")->prepare("SELECT MIN(counter) FROM user_queue");
        $statementMinCount->execute();
        $minCount = $statementMinCount->fetch(PDO::FETCH_COLUMN);
        return $position - $minCount;
    }

    /**
     * Wether or not the user is already being processed
     *
     * @param  string    $username
     * @return boolean
     */
    private static function shouldAddToQueue(string $username) {
        if (UserfavHistory::userIsInDb($username)) {
            return false;
        }
        $statement = SqlConnection::get("e621")->prepare("SELECT user_name FROM user_queue WHERE user_name = :user");
        $statement->bindValue("user", $username);
        $statement->execute();
        return $statement->fetch() !== false ? false : true;
    }

    /**
     * Adds a user to the queue, if he's not already processed or in the queue
     *
     * @param  string $username
     * @return void
     */
    public static function addToQueue(string $username) {
        if (!self::shouldAddToQueue($username)) {
            return;
        }
        $statement = SqlConnection::get("e621")->prepare("INSERT INTO user_queue (user_name) VALUES (:user)");
        $statement->bindValue("user", $username);
        $logger = Logger::get(self::$logfile);
        if ($statement->execute() === false) {
            $logger->log(LogLevel::ERROR, "Failed to add {$username} to queue");
        } else {
            $logger->log(LogLevel::INFO, "Added {$username} to queue");
        }
        return;
    }

    /**
     *  Returns an array of usernames ordered by their appearance in the queue
     *
     * @return string[]
     */
    public static function getFullQueue(): array{
        $statement = SqlConnection::get("e621")->prepare("SELECT user_name FROM user_queue ORDER BY counter");
        $statement->execute();
        $result = [];

        while (($username = $statement->fetch(PDO::FETCH_COLUMN)) !== false) {
            $result[] = $username;
        }
        return $result;
    }

    /**
     * Removes the specified user from the queue
     *
     * @param  string $username
     * @return void
     */
    public static function removeFromQueue(string $username) {
        $statement = SqlConnection::get("e621")->prepare("DELETE FROM user_queue WHERE user_name = :user");
        $statement->bindValue("user", $username);
        if ($statement->execute() === false) {
            $logger = Logger::get(self::$logfile);
            $logger->log(LogLevel::ERROR, "Failed to remove {$username} from queue");
        }
    }
}