async function fetchFavorites(){
    const username = document.getElementById("name").value;
    const favs = JSON.parse(await postURL("/serverside/projects/e621history/fetchFavs.php", {username: username}));
    
}



function postURL(url, data) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest();
        request.open("POST", url, true);
        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                resolve(request.responseText);
            } else { reject(); }
        };
        request.onerror = () => { reject() };
        request.send(JSON.stringify(data));
    })
}