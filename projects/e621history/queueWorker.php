<?php
$fp = fopen('/tmp/php-commit.lock', 'c');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    file_put_contents("test.log", "no lock");
    exit;
}
file_put_contents("test.log", "lock aquired");
sleep(5);
flock($fp, LOCK_UN);
