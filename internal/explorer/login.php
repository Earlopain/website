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
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <script src="js/serverActions.js"></script>
    <link rel="stylesheet" type="text/css" href="css/loginstyle.css">
    <title>Login Page</title>
</head>
<body>
    <form class="loginform" onsubmit="loginAndGotoIndex(); return false">
        <div>
            <label for="username">Username</label>
            <input type="text" id="username">
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" autocomplete="off">
        </div>
        <div>
            <input type="submit" value="Login"></button>
        </div>
    </form>
</body>

</html>
