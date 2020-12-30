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
    <div id="container">
        <div id=buttoncontainer>
            <button data-type="command" data-extra="e621dl">E621 Download</button>
            <button data-type="getfile" data-extra="e621pools">E621 Edit Comics</button>
            <button data-type="command" data-extra="plexrefreshcomics">E621 Refresh Comics</button>
            <button data-type="command" data-extra="e621replace">E621 Replacer</button>
            <button data-type="command" data-extra="sofurryepub">Sofurry EPUB</button>
            <div></div>
            <button data-type="command" data-extra="deezerdl">Deezer Download</button>
            <button data-type="command" data-extra="musicvideo">Youtube Musicvideo</button>
            <button data-type="command" data-extra="shortmovie">Short Movies</button>
            <button data-type="command" data-extra="youtube">Youtube Download</button>
            <div></div>
            <button data-type="command" data-extra="apache2restart">Apache2 Restart</button>
            <button data-type="command" data-extra="plexrestart">Plex Restart</button>
            <button data-type="command" data-extra="plextagimages">Plex Tag Images</button>
            <button data-type="command" data-extra="plexfixdates">Plex Fix Dates</button>
            <button data-type="getfile" data-extra="smloadrconfig">Deezer Set ARL</button>
            <button data-type="savefile" id="submitfile" style="display: none">Submit File</button>
        </div>
        <div id="outputcontainer">
            <textarea id="textarea"></textarea>
        </div>
    </div>
</body>

</html>
