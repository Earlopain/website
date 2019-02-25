<?php
 ini_set('display_errors',1); 
 error_reporting(E_ALL);
function getSecret($wished){
    $wished = strtolower($wished);
    $string = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/serverside/secrets.txt');
    $array = explode("\n", $string);
    foreach ($array as $line) {
        $result = explode("=", $line);
        $key = reset($result);
        $value = end($result);
        if($wished == strtolower($key))
            return $value;
    }
    throw new Exception("No key like ".$wished);
}