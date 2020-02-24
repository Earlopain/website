<?php

set_time_limit(1);
if (isset($_POST["code"])) {
    $lines = explode("\n", $_POST["code"]);
    foreach ($lines as $key => $line) {
        $substr = substr($line, 0, 5);
        if ($substr === "echo " || $substr === "print") {
            $lines[$key] = $line . "echo '<br>';";
        }
    }
    $lines = implode("\n", $lines);

    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w")
    );
    $extensionDir = ini_get("extension_dir");
    $process = proc_open("php -f eval.php " . base64_encode($lines) . " 2>&1", $descriptorspec, $pipes);
    register_shutdown_function(function () use ($process) {
        if (is_resource($process)) {
            proc_terminate($process, 9);
        }
    });
    usleep(100000); //0.1 seconds
    if (is_resource($process)) {
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        $stream = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        echo $stream;
        $procStatus = proc_get_status($process);
        if ($procStatus["running"] === true) {
            echo "Process took too long and was terminated";
            proc_terminate($process, 9);
        }
    }
}
