async function fetchCsv() {
    const username = document.getElementById("username").value;
    const tagGroups = {
        "gay": ["male/male -bisexual -male/female", "male solo -bisexual"],
        "straight": ["male/female -bisexual", "female solo"]
    }
    const files = document.getElementById("folderinput").files;
    let fileDates = {};
    for (const file of files) {
        fileDates[file.name.split(".")[0]] = file.lastModified;
    }

    const url = "getUserFavHistory.php";

    const response = await postURL(url, {
        username: username,
        tagGroups: tagGroups,
        fileDates: fileDates
    });
    const json = JSON.parse(response.response);
    const maxDataPoints = json.xAxis.length;
    console.log(maxDataPoints)
    for (const groupName of Object.keys(json.tagGroups)) {
        console.log(groupName);
        console.log(json.tagGroups[groupName][maxDataPoints - 1]);
    }

    document.getElementById("csv").innerHTML = response.response;
}
