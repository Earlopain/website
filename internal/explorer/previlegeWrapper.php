<?php
$user = prepareString($_REQUEST["user"]);
$passwd = prepareString($_REQUEST["password"]);
echo shell_exec("sudo php -f /media/plex/html/internal/explorer/validatePassword.php '{$user}' '{$passwd}'");

function prepareString(string $string) {
    $split = explode("'", $string);
    return implode("'\\''", $split);
}
