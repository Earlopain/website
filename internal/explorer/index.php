<?php
session_start();
if (!isset($_SESSION["uid"])) {
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
    <div class="tablecontainer">
        <div class="tabletoprow">
            <div class="divcontainer">
                <input id="currentfolder" type="text" value="/">
            </div>
            <div class="divcontainer options">
                <input type="button" value="download" onclick="downloadSelection()">
                <input type="button" value="delete">
                <input type="button" value="upload">
                <input type="text" id="username">
                <input type="password" id="password">
                <input type="button" value="login" onclick="loginAndReloadFolder()">
            </div>
            <div class="divcontainer">
                Logged in as: <span id="loggedinas"></span>
            </div>
            <div class="slidecontainer">
                <input type="range" min="50" max="500" class="slider" id="filenameslider">
            </div>
        </div>
        <table class="table">
            <colgroup span="5"></colgroup>
            <tr id="tableheader">
                <th class="checkbox"></th>
                <th class="filename">name</th>
                <th class="ext">ext</th>
                <th class="size">size</th>
                <th class="user">user</th>
                <th class="group">group</th>
                <th class="perms">rwx</th>
                <th class="readable">read</th>
                <th class="writeable">write</th>
            </tr>
            <tbody id="filecontents">
            </tbody>
        </table>
    </div>
    <div id="editor"></div>
</body>

</html>
