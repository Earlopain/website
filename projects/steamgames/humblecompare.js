async function startCompare() {
    const steamid = document.getElementById('textfield').value;
    const steamuser = await SteamUser.create(steamid);
    const json = await steamuser.getGames();
    const games = json.games.map(x => { return x.appid });
    const humbleData = JSON.parse(await getURL("/projects/steamgames/humble.json"));
    let missingCount = 0;
    let totalCount = 0

    let container = document.getElementById("container");
    container.innerHTML = "";
    humbleData.forEach(month => {
        const time = month.name;
        let missing = [];

        month.appids.forEach(appid => {
            totalCount++;
            if (!games.includes(appid)) {
                missing.push(appid);
                missingCount++;
            }
        });
        
        let div = document.createElement("div");
        div.appendChild(document.createTextNode(time));
        div.appendChild(document.createElement("p"));
        if (missing.length === 0){
            div.appendChild(document.createTextNode("Completed"))
            container.appendChild(div);
            return;
        }
        missing.forEach(element => {
            if (typeof element === "string") {
                div.appendChild(document.createTextNode(element));
                return
            }
            let a = document.createElement("a");
            a.href = "https://store.steampowered.com/app/" + element;
            let img = document.createElement("img");
            img.src = "https://steamcdn-a.akamaihd.net/steam/apps/" + element + "/header.jpg"
            a.appendChild(img);
            div.appendChild(a);
        });
        container.appendChild(div);

    });
    console.log("Missing " + missingCount + " out of " + totalCount);
}