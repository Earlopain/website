<?php
$action = $argv[1];

switch ($action) {
    case "validatePassword":
        login($argv);
        break;
    case "getdir":
        break;
}

function login($argv) {
    set_error_handler(function() {die("false");}, E_ALL); 
    $user = $argv[2];
    $password = $argv[3];
    $file = fopen("/etc/shadow", "r");
    if ($file === false) {
        exitProcess($file);
    }
    while (!feof($file)) {
        $line = fgets($file);
        $split = explode(":", $line);
        if ($split[0] === $user) {
            $passwordSplit = explode("$", $split[1]);
            $algorithm = $passwordSplit[1];
            $salt = $passwordSplit[2];
            $compairAgainst = exec("openssl passwd -{$algorithm} -salt '{$salt}' '{$password}'");
            if (strcmp($compairAgainst, $split[1]) !== 0) {
                exitProcess($file);
            }
            echo posix_getpwnam($user)["uid"];
            exit();
        }
    }
    exitProcess($file);
}

function exitProcess($file) {
    echo "false";
    if ($file !== false) {
        fclose($file);
    }
    exit();
}
