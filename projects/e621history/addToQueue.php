<?php

require_once "userFavHistory.php";
UserfavHistory::addToQueue($_REQUEST["username"]);
