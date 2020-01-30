<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <title>UwU</title>
    <script src="/projects/util.js"></script>
    <script src="fetch.js"></script>
    <script src='https://cdn.plot.ly/plotly-latest.min.js'></script>
</head>

<body>
    Your username: <input type="text" id="username">
    Optional local files<input type="file" id="folderinput" webkitdirectory directory multiple/>
    <button onclick="fetchCsv()">Fetch Favs</button>
    <br>
    <div id="graph" style="width: 50%"></div>
</body>

</html>
