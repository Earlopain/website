<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <title>UwU</title>
    <link rel="stylesheet" href="style.css">
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
    <div id="taggroups">
        <?php echo generateDefaultTagGroups() ?>
    </div>
    <div id="graph"></div>
</body>
</html>


<?php

function generateDefaultTagGroups() {
    $result = "";
    $defaultTagGroups = ["gay" => ["male/male -bisexual -male/female", "male solo -bisexual"],
        "straight" => ["male/female -bisexual -male/male", "female solo -bisexual"]];
    foreach ($defaultTagGroups as $tagGroupName => $tagGroupFilter) {
        $result .= "<div class='taggroupcontainer' id='{$tagGroupName}'>";
        $result .= "<div class='taggroupname'>{$tagGroupName}</div>";
        foreach ($tagGroupFilter as $index => $filterContent) {
            $result .= "<div class='singletaggroup' id='{$tagGroupName}_{$index}'>";
            $allFilters = explode(" ", $filterContent);
            foreach ($allFilters as $index2 => $singleFilter) {
                $result .= "<div class='singlefilter'>{$singleFilter}</div>";
            }
            $result .= "</div>";
        }
        $result .= "</div>";
    }
    return $result;
}
?>
