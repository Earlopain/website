<?php
session_start();
if (isset($_SESSION["uid"])) {
    require_once "htmlHelper.php";
    redirectToFolderOfFile();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "htmlHelper.php"; generateHeadBoilerplate(); ?>
    <script src="serverActions.js"></script>
    <title>Login Page</title>
</head>
<label for="username">Username:</label>
<input type="text" id="username">
<label for="password">Password:</label>
<input type="text" id="password">
<button onclick="loginAndGotoIndex()">Login</button>
<body>
</body>

</html>
