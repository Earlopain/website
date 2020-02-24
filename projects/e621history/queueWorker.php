<?php
$fp = fopen('/tmp/php-commit.lock', 'c');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    exit;
}

require_once "userFavHistory.php";
require_once "queue.php";

while (count($allUsers = E621UserQueue::getFullQueue()) > 0) {
    foreach ($allUsers as $username) {
        UserfavHistory::populateDb($username);
        E621UserQueue::removeFromQueue($username);
    }
}

flock($fp, LOCK_UN);
