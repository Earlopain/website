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
        <button class="button" onclick="executeOnServer('apache2restart')">Apache2 Restart</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('plexrestart')">Plex Restart</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('plexrefreshcomics')">Plex Refresh Comics</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="getFileFromServer('/media/plex/software/e621comics/pools.json')">Plex Edit Comics</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('plextagimages')">Plex Tag Images</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('plexfixdates')">Plex Fix Dates</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('deezerdl')">Deezer Download</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="getFileFromServer('/media/plex/software/deezerdl/SMLoadrConfig.json')">Deezer Set ARL</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('e621dl')">E621 Download</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('musicvideo')">Youtube Musicvideo</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('shortmovie')">Short Movies</button>
    </div>
    <div class="buttoncontainer">
        <button class="button" onclick="executeOnServer('youtube')">Youtube Download</button>
    </div>
    <p></p>
    <div class="buttoncontainer">
        <textarea id="commandout" class="output button"></textarea>
        <button id="submitfile" class="button" onclick="putFileOnServer()" style="display: none">Submit File</button>
    </div>

</body>

</html>
