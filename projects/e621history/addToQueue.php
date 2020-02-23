<?php

require_once "queue.php";
E621UserQueue::addToQueue($_REQUEST["username"]);

closeConnection();
exec("php -f queueWorker.php");
