async function displayGraph(){
    const favs = fetchFavorites();
}

async function fetchFavorites(){
    const username = document.getElementById("name").value;
    const favs = JSON.parse(await postURL("/serverside/projects/e621history/fetchFavs.php", {username: username}));
    return favs;
}