const proxyURL = "/serverside/projects/steamgames/apiwrapper.php"
const steamURL = "https://api.steampowered.com/";

//TODO: automaticly convert vanity to id
//Make class?

async function getGames(steamid){
    return await getWrapper("IPlayerService/GetOwnedGames/v1/?format=json&include_appinfo=1&include_played_free_games=1&appids_filter=&steamid=" + steamid);
}




async function getWrapper(url){
    const request = await postURL(proxyURL, {url: steamURL + url});
    return request.response;
}