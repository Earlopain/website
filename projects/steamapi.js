const proxyURL = "/serverside/projects/steamgames/apiwrapper.php"
const steamURL = "https://api.steampowered.com/";

//TODO: automaticly convert vanity to id

async function getGames(steamid){
    const response =  await postURL(proxyURL, {url: steamURL + "IPlayerService/GetOwnedGames/v1/?format=json&include_appinfo=1&include_played_free_games=1&appids_filter=&steamid=" + steamid})
    return response;
}