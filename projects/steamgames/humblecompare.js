async function startCompare(){
    const steamid = document.getElementById('textfield').value;
    const json = JSON.parse(await getGames(steamid));
    const games = json.response.games.map(x => {return x.appid});
    const humbleData = JSON.parse(await getURL("/projects/steamgames/humble.json"));
    let missingCount = 0;
    let totalCount = 0
    humbleData.forEach(month => {
        month.appids.forEach(appid => {
            totalCount++;
            if(!games.includes(appid)){
                console.log("Missing " + appid)
                missingCount++;
            }
        });
    });
    console.log("Missing " + missingCount + " out of " + totalCount);
}