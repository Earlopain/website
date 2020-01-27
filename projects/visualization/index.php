<!DOCTYPE html>
<html>

<head>
    <?php require_once "htmlHelper.php";
generateHeadBoilerplate();?>
    <title>Some Stuff</title>
    <script src="/projects/util.js"></script>
    <script src="https://d3js.org/d3.v5.min.js"></script>
    <link rel="stylesheet" href="discord.css">

</head>

<body>
    <div class="Invite ID">
        <label>Available Servers:</label>
        <select id="dropdownall" onchange="changeGraph()"></select>
        Server Invite: <input type="text" id="textfield">
        <button onclick="submitNew()">Submit</button>
        Missing: <select id="dropdownmissing"></select>
        <p></p>
        Checks discord invite link every 20s or so and puts time and count in a .cvs file available raw <a href="discordoutput/41771983423143937.csv">like
            this</a>
        <p></p>
        <div>
            <label>Joined in the last hour: </label><label id="lasthour" class="count">temp</label>
            <label>Last 6 hours: </label><label id="last6hours" class="count">temp</label>
            <label>Last 12 hours: </label><label id="last12hours" class="count">temp</label>
            <label>Last day: </label><label id="lastday" class="count">temp</label>
            <label>Since start:</label><label id="total" class="count">temp</label>
            <label>Current count:</label><label id="currentcount" class="count">temp</label>
        </div>

    </div>
    <script src="visualize.js"></script>
    <svg id="svg"></svg>

</body>

</html>
