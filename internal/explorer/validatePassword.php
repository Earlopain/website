<?php
$user = $argv[1];
$password = $argv[2];
$file = fopen("/etc/shadow", "r");

while (!feof($file)) {
    $line = fgets($file);
    $split = explode(":", $line);
    if ($split[0] === $user) {
        $passwordSplit = explode("$", $split[1]);
        $algorithm = $passwordSplit[1];
        $salt = $passwordSplit[2];
        $compairAgainst = exec("openssl passwd -{$algorithm} -salt {$salt} {$password}");
        echo strcmp($compairAgainst, $split[1]) === 0 ? "true" : "false";
        fclose($file);
        return;
    }
}
echo "false";
fclose($file);
