<?php

set_time_limit(0.1);

if (isset($_POST["code"])) {
    $lines = explode("\n", $_POST["code"]);
    foreach ($lines as $key => $line) {
        $substr = substr($line, 0, 5);
        if($substr === "echo " || $substr === "print"){
            $lines[$key] = $line . "echo '<br>';";
        }
    }
    eval(implode("\n", $lines));
}
