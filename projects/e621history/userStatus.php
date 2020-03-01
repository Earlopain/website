<?php

require_once "userFavHistory.php";
require_once "queue.php";
require_once "e621user.php";

$result = new stdClass();
E621User::addToDb($_GET["username"]);
$userid = E621User::usernameToId($_GET["username"]);
if ($userid === -1) {
    $result->text = "Unknown username";
    $result->code = 0;
} else if (E621User::userIsProcessed($userid)) {
    $result->text = "Added to db, fetching result...";
    $result->code = 0;
} else {
    $queuePosition = E621UserQueue::queuePosition($userid);
    if ($queuePosition === 0) {
        $postCount = UserfavHistory::countPostsInDb($userid);
        $result->text = "Found {$postCount} of your posts";
        $result->code = 1;
    } else if ($queuePosition !== -1) {
        $result->text = "You are position " . ($queuePosition + 1) . " in the queue";
        $result->code = 1;

    } else {
        $result->text = "You are not in the queue";
        $result->code = 2;
    }
}

echo json_encode($result);
