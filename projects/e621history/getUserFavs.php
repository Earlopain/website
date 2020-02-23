<?php
require_once "userFavHistory.php";

$postParams = PostParams::create();
$favs = new UserfavHistory($postParams);
echo $favs->generateGraph();
