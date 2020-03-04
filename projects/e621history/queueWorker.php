<?php
$fp = fopen('/tmp/php-commit.lock', 'c');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    exit;
}

require_once "userFavHistory.php";
require_once "queue.php";
require_once "e621user.php";
require_once "logger.php";

while (count($allUsers = E621UserQueue::getFullQueue()) > 0) {
    foreach ($allUsers as $userid) {
        $username = E621User::useridToName($userid);
        if ($username === "") {
            Logger::log(LOG_ERR, "Unkown userid " . $userid . " in queue");
            continue;
        }
        UserfavHistory::populateDb($userid, $username);
        E621UserQueue::removeFromQueue($userid);
    }
}

flock($fp, LOCK_UN);
