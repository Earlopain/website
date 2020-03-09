<?php

function generateHeadBoilerplate() {
    $tmp = explode(".", $_SERVER["SERVER_NAME"]);
    $tmp = array_reverse($tmp);
    $domain = $tmp[1] . "." . $tmp[0];
    $prefix = $_SERVER["REQUEST_SCHEME"] . "://" . $domain;
    echo <<< "HTML"
    <meta charset="utf-8" />
    <!--Favicon stuff-->
    <link rel="apple-touch-icon" sizes="180x180" href="{$prefix}/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="{$prefix}/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="{$prefix}/favicons/favicon-16x16.png">
    <link rel="manifest" href="{$prefix}/favicons/site.webmanifest">
    <link rel="mask-icon" href="{$prefix}/favicons/safari-pinned-tab.svg" color="#00cc4a">
    <link rel="shortcut icon" href="{$prefix}/favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="msapplication-config" content="{$prefix}/favicons/browserconfig.xml">
    <meta name="theme-color" content="#00cc4a">
    <link rel="stylesheet" href="{$prefix}/global.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
HTML;
}

function redirectToFolderOfFile() {
    $currentUrl = explode("/", $_SERVER["REQUEST_URI"]);
    array_pop($currentUrl);
    header("Location: " . implode("/", $currentUrl));
}
