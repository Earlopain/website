async function fetchCsv() {
    const username = document.getElementById("username").value;
    const tagGroups = {
        "gay": ["male/male -bisexual -male/female", "male solo -bisexual"],
        "straight": ["male/female -bisexual", "female solo"]
    }

    var url = "getUserFavHistory.php";

    const response = await postURL(url, {
        username: username,
        tagGroups: tagGroups
    });
    document.getElementById("csv").innerHTML = response.response;
}
