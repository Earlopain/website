<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <title>UwU</title>
    <script src="/projects/owotext/convert.js"></script>
</head>

<body>
    <textarea rows="4" cols="50" id="input"></textarea><p></p>
    <button onclick="document.getElementById('output').value = convertText(getElementById('input').value)">Convert</button><p></p>
    <textarea rows="4" cols="50" id="output"></textarea>
</body>

</html>
