<?php
    session_start();
    if(!isset($_SESSION["uid"])){
        header("Location: login.php");
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "htmlHelper.php"; generateHeadBoilerplate(); ?>
    <link rel="stylesheet" href="style.css">
    <script src="display.js"></script>
    <script src="serverActions.js"></script>
    <script src="editor.js"></script>
    <script src="tablesort.js"></script>
    <title>Internal</title>
</head>

<body>
    <div id="toprow">
        <input id="currentfolder" type="text" value="/">
        <input type="button" value="download" onclick="downloadSelection()">
        <input type="button" value="delete">
        <input type="button" value="upload">
        <input type="text" id="username">
        <input type="password" id="password">
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
