<?php

require_once "sql.php";
require_once "userFavHistory.php";
require_once "logger.php";
require_once "e621user.php";

class E621UserQueue {
    private static $logfile = "queue.log";
    /**
     * Returns the queue index of the user
     * Will be -1 if not in queue
     *
     * @param  int     $userid
     * @return integer queue index
     */
    public static function queuePosition(int $userid): int {
        $statementPosition = SqlConnection::get("e621")->prepare("SELECT counter FROM user_queue where user_id = :userid");
        $statementPosition->bindValue("userid", $userid);
        $statementPosition->execute();
        $position = $statementPosition->fetch(PDO::FETCH_COLUMN);
        //Not in queue
        if ($position === false) {
            Logger::log(self::$logfile, LOG_INFO, "User {$userid} not in queue");
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
     * @param  int       $userid
     * @return boolean
     */
    private static function shouldAddToQueue(int $userid) {
        $connection = SqlConnection::get("e621");
        $statementProcessed = $connection->prepare("SELECT user_id FROM processed_users WHERE user_id = :userid");
        $statementProcessed->bindValue("userid", $userid);
        $statementProcessed->execute();
        $statementQueue = $connection->prepare("SELECT user_id FROM user_queue WHERE user_id = :userid");
        $statementQueue->bindValue("userid", $userid);
        $statementQueue->execute();
        return $statementProcessed->fetch() === false && $statementQueue->fetch() === false;
    }

    /**
     * Adds a user to the queue, if he's not already processed or in the queue
     *
     * @param  int    $userid
     * @return void
     */
    public static function addToQueue(int $userid) {
        if (!self::shouldAddToQueue($userid)) {
            return;
        }
        $statement = SqlConnection::get("e621")->prepare("INSERT INTO user_queue (user_id) VALUES (:userid)");
        $statement->bindValue("userid", $userid);
        if ($statement->execute() === false) {
            Logger::log(self::$logfile, LOG_ERR, "Failed to add {$userid} to queue");
        } else {
            Logger::log(self::$logfile, LOG_INFO, "Added {$userid} to queue");
        }
        return;
    }

    /**
     *  Returns an array of usernames ordered by their appearance in the queue
     *
     * @return string[]
     */
    public static function getFullQueue(): array{
        $statement = SqlConnection::get("e621")->prepare("SELECT user_id FROM user_queue ORDER BY counter");
        $statement->execute();
        $result = [];

        while (($userid = $statement->fetch(PDO::FETCH_COLUMN)) !== false) {
            $result[] = $userid;
        }
        return $result;
    }

    /**
     * Removes the specified user from the queue
     *
     * @param  int    $userid
     * @return void
     */
    public static function removeFromQueue(int $userid) {
        $statement = SqlConnection::get("e621")->prepare("DELETE FROM user_queue WHERE user_id = :userid");
        $statement->bindValue("userid", $userid);
        if ($statement->execute() === false) {
            Logger::log(self::$logfile, LOG_ERR, "Failed to remove {$userid} from queue");
        }
    }
}
