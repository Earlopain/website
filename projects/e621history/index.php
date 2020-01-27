<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <title>UwU</title>
    <script src="/projects/util.js"></script>
    <script src="/projects/e621history/favorites.js"></script>
</head>

<body>
    Your username: <input type="text" id="name">
    Optional local files<input type="file" id="ctrl" webkitdirectory directory multiple/>
    <button onclick="displayGraph()">Fetch Favs</button>
</body>

</html>
