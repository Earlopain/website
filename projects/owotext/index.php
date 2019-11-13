<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <!--Favicon stuff-->
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <link rel="manifest" href="/favicons/site.webmanifest">
    <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#00cc4a">
    <link rel="shortcut icon" href="/favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#2b5797">
    <meta name="msapplication-config" content="/favicons/browserconfig.xml">
    <meta name="theme-color" content="#00cc4a">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>UwU</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="/projects/owotext/convert.js"></script>

    <link rel="stylesheet" href="/css/darkmode.css">

</head>

<body>
    <textarea rows="4" cols="50" id="input"></textarea><p></p>
    <button onclick="document.getElementById('output').value = convertText(getElementById('input').value)">Convert</button><p></p>
    <textarea rows="4" cols="50" id="output"></textarea>
</body>

</html>
