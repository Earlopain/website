<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <link rel="stylesheet" href="style.css">
    <script src="main.js"></script>
    <title>Internal</title>
</head>

<body>
    <div class="buttoncontainer">
        <button onclick="executeOnServer('e621dl')">E621 Download</button>
        <button onclick="getFileFromServer('/media/plex/software/e621comics/pools.json')">E621 Edit Comics</button>
        <button onclick="executeOnServer('plexrefreshcomics')">E621 Refresh Comics</button>
        <button onclick="executeOnServer('e621replace')">E621 Replacer</button>
    </div>
    <div class="buttoncontainer">
        <button onclick="executeOnServer('deezerdl')">Deezer Download</button>
        <button onclick="executeOnServer('musicvideo')">Youtube Musicvideo</button>
        <button onclick="executeOnServer('shortmovie')">Short Movies</button>
        <button onclick="executeOnServer('youtube')">Youtube Download</button>
    </div>
    <div class="buttoncontainer">
        <button onclick="executeOnServer('apache2restart')">Apache2 Restart</button>
        <button onclick="executeOnServer('plexrestart')">Plex Restart</button>
        <button onclick="executeOnServer('plextagimages')">Plex Tag Images</button>
        <button onclick="executeOnServer('plexfixdates')">Plex Fix Dates</button>
        <button onclick="getFileFromServer('/srv/http/.config/smloadr/SMLoadrConfig.json')">Deezer Set ARL</button>
    </div>
    <div class="outputcontainer">
        <textarea id="textarea"></textarea>
        <button id="submitfile" onclick="putFileOnServer()" style="display: none">Submit File</button>
    </div>
</body>

</html>
