<?php

require_once "sql.php";
require_once "util.php";
require_once "e621user.php";

class E621User {
    /**
     * Adds a user to the db
     *
     * @param  string $username
     * @return bool
     */
    public static function addToDb(string $username): bool {
        $connection = SqlConnection::get("e621");
        if (self::usernameIsInDb($username)) {
            return false;
        }
        $json = getJson("https://e621.net/users/{$username}.json", ["user-agent" => "earlopain"]);
        if ($json === null || $json === NETWORK_ERROR) {
            return false;
        }

        $statement = $connection->prepare("INSERT INTO users (user_id, user_name, last_updated) VALUES (:userid, :username, NOW())");
        $statement->bindValue("userid", $json->id);
        $statement->bindValue("username", $json->name);
        return $statement->execute();
    }

    /**
     * Checks if a user has already been added to the db
     *
     * @param  string $username
     * @return bool
     */
    public static function usernameIsInDb(string $username): bool {
        $statement = SqlConnection::get("e621")->prepare("SELECT user_name FROM users WHERE LOWER(user_name) = :username");
        $statement->bindValue("username", strtolower($username));
        $statement->execute();
        return $statement->fetch() !== false;
    }

    /**
     * Checks if a user has already been added to the db
     *
     * @param  integer $userid
     * @return bool
     */
    public static function useridIsInDb(int $userid): bool {
        $statement = SqlConnection::get("e621")->prepare("SELECT user_id from users WHERE user_id = :userid");
        $statement->bindValue("userid", $userid);
        $statement->execute();
        return $statement->fetch() !== false;
    }

    /**
     * Checks if a users has his favorites finished
     *
     * @param  integer   $userid
     * @return boolean
     */
    public static function userIsProcessed(int $userid): bool {
        $connection = SqlConnection::get("e621");
        $statement = $connection->prepare("SELECT user_id from processed_users WHERE user_id = :userid");
        $statement->bindValue("userid", $userid);
        $statement->execute();
        return $statement->fetch() !== false;
    }

    /**
     * Converts a username to userid. Returns -1 on unknown user
     *
     * @param  string $username
     * @return int
     */
    public static function usernameToId(string $username): int {
        $connection = SqlConnection::get("e621");
        $statement = $connection->prepare("SELECT user_id from users WHERE LOWER(user_name) = :username");
        $statement->bindValue("username", strtolower($username));
        $statement->execute();
        $result = $statement->fetchColumn(0);
        return $result !== false ? $result : -1;
    }

    /**
     * Converts a userid to username. Returns empty string on unknown user
     *
     * @param  integer  $id
     * @return string
     */
    public static function useridToName(int $id): string {
        $connection = SqlConnection::get("e621");
        $statement = $connection->prepare("SELECT user_name from users WHERE user_id = :userid");
        $statement->bindValue("userid", $id);
        $statement->execute();
        $result = $statement->fetchColumn(0);
        return $result !== false ? $result : "";
    }
}
