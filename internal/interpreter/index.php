<!DOCTYPE html>
<html lang="en">

<head>
    <?php require_once "htmlHelper.php"; generateHeadBoilerplate(); ?>
    <link rel="stylesheet" href="style.css">
    <title>Interpreter</title>
</head>

<body>
    <div style="height: 100%">
        <textarea id="input"></textarea>
        <form style="height: 0%" id="form" action="iframe.php" method="post" target="iframe">
            <textarea style="display: none" name="code"></textarea>
        </form>
        <div id="output"></div>
    </div>
    <iframe style="display: none" id="iframe" name="iframe" src="iframe.php"></iframe>
    <script src="submit.js"></script>
</body>

</html>
