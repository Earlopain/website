<?php
function proxyGetUrl($url){
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:65.0) Gecko/20100101 Firefox/65.0");
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
    $response =  curl_exec($c);
    return $response;
}