const proxyURL = "/projects/steamgames/apiwrapper.php"
const steamURL = "https://api.steampowered.com/";

class SteamUser {
    constructor(steamid) {
        this.steamid = steamid;
    }
    static async create(steamid) {
        //not a steamid, but rather vanity url
        if (steamid.length !== 17 || !(/^\d+$/.test(steamid))) {
            steamid = await this.resolveVanity(steamid);
        }
        return new SteamUser(steamid);
    }

    async  getGames() {
        return await getWrapper("IPlayerService/GetOwnedGames/v1/?format=json&include_appinfo=1&include_played_free_games=1&appids_filter=&steamid=" + this.steamid);
    }

    static async resolveVanity(vanity) {
        const response = await getWrapper("ISteamUser/ResolveVanityURL/v1/?url_type=1&vanityurl=" + vanity);
        if (response.success != 1)
            return undefined;
        return response.steamid;
    }

}

async function getWrapper(url) {
    const request = await postURL(proxyURL, { url: steamURL + url });
    if (request.status !== 200) {
        console.log(url)
        throw new Error(url + "\n" + response.responseText);
    }
    return JSON.parse(request.response).response;
}
