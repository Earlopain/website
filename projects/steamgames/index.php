<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php"; generateHeadBoilerplate(); ?>
    <title>Steam Stuff</title>
    <script src="/projects/steamapi.js"></script>
    <script src="/projects/util.js"></script>
    <script src="/projects/steamgames/humblecompare.js"></script>
</head>

<body>
        Steam ID: <input type="text" id="textfield">
        <button onclick="startCompare()">Submit</button>
        <div id="container"></div>

</body>

</html>
