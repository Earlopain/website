<?php

require_once "queue.php";
require_once "e621user.php";
E621User::addToDb($_REQUEST["username"]);
$userid = E621User::usernameToId($_REQUEST["username"]);
if ($userid === -1) {
    return;
}
E621UserQueue::addToQueue($userid);

closeConnection();
exec("php -f queueWorker.php");
