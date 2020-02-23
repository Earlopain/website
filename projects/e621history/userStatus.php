<?php

require_once "userFavHistory.php";
require_once "queue.php";

$result = new stdClass();

if (UserfavHistory::userIsInDb($_GET["username"])) {
    $result->text = "Added to db, fetching result...";
    $result->code = 0;
} else {
    $queuePosition = E621UserQueue::queuePosition($_GET["username"]);
    if ($queuePosition !== -1) {
        $result->text = "You are position " . ($queuePosition + 1) . " in the queue";
        $result->code = 1;

    } else {
        $result->text = "You are not in the queue";
        $result->code = 2;
    }
}

echo json_encode($result);
