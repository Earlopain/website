async function displayGraph() {
    const favs = fetchFavorites();
}

async function fetchFavorites() {
    const username = document.getElementById("name").value;
    const response = await postURL("/serverside/projects/e621history/fetchFavs.php", { username: username });
    logResponse(response);
    if (response.status !== 200)
        return {};
    return JSON.parse(response.responseText);
}