<?php
set_time_limit(1);
ini_set("html_errors", 1);
eval(base64_decode($argv[1]));
