<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <title>UwU</title>
    <script src="/projects/util.js"></script>
    <script src="fetch.js"></script>
    <script src="statusChecker.js"></script>
    <script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
</head>

<body>
    Your username: <input type="text" id="username">
    <button onclick="startLoop()">Fetch Favs</button>
    <br>
    Optional local files: <input type="file" id="folderinput" onclick="document.getElementById('folderinput').value = ''" webkitdirectory directory multiple/>
    <br>
    <div id="graph"></div>
</body>

</html>
