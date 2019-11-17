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
        </div>
        <table class="table">
            <colgroup span="5"></colgroup>
            <tr id="tableheader">
                <th class="checkbox"></th>
                <th class="filename">Name</th>
                <th class="ext">Ext</th>
                <th class="user">User</th>
                <th class="group">Group</th>
                <th class="perms">Perms</th>
                <th class="size">Size</th>
                <th class="readable">Readable</th>
                <th class="writeable">Writable</th>
            </tr>
            <tbody id="filecontents">
            </tbody>
        </table>
    </div>
    <div id="editor"></div>
</body>

</html>
