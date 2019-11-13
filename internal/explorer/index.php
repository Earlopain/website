<?php
    session_start();
    if(!isset($_SESSION["uid"])){
        header("Location: login.html");
        die();
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <!--Favicon stuff-->
    <link rel="apple-touch-icon" sizes="180x180" href="https://c5h8no4na.net/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="https://c5h8no4na.net/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="https://c5h8no4na.net/favicons/favicon-16x16.png">
    <link rel="manifest" href="https://c5h8no4na.net/favicons/site.webmanifest">
    <link rel="mask-icon" href="https://c5h8no4na.net/favicons/safari-pinned-tab.svg" color="#00cc4a">
    <link rel="shortcut icon" href="https://c5h8no4na.net/favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="msapplication-config" content="https://c5h8no4na.net/favicons/browserconfig.xml">
    <meta name="theme-color" content="#00cc4a">

    <link rel="stylesheet" href="https://c5h8no4na.net/css/darkmode.css">
    <link rel="stylesheet" href="style.css">

    <script src="display.js"></script>
    <script src="serverActions.js"></script>
    <script src="editor.js"></script>
    <script src="tablesort.js"></script>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Internal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <div id="toprow">
        <input id="currentfolder" type="text" value="/">
        <input type="button" value="download" onclick="downloadSelection()">
        <input type="button" value="delete">
        <input type="button" value="upload">
        <input type="text" id="username">
        <input type="text" id="password">
        <input type="button" value="login" onclick="login()">
    </div>
    <div id="container">
        <table id="table">
            <colgroup span="5"></colgroup>
            <tr id="tableheader">
                <th></th>
                <th style="max-width: 200px;">Name</th>
                <th>Ext</th>
                <th>User</th>
                <th>Group</th>
                <th>Perms</th>
                <th>Size</th>
                <th>Readable</th>
                <th>Writable</th>
            </tr>
            <tbody id="filecontents">
            </tbody>
        </table>
        <div id="editor"></div>
    </div>
</body>

</html>