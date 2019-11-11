<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <script>
        let output = document.getElementById("output");
        let resultString = "";
        eval(`<?php echo $_POST["code"] ?>`);
        clearTimeout(parent.timeout);
        parent.document.getElementById("output").innerHTML = resultString;
    </script>
</body>
</html>
