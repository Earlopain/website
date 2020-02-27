async function checkStatus(username) {
    const url = "userStatus.php?username=" + username;
    return JSON.parse(await getURL(url));
}

let lastUsername;
let loopIntervalId;

async function startLoop() {
    const username = document.getElementById("username").value;
    if (username === lastUsername) {
        infoMessage("already processing", "info");
        return;
    }
    //Don't allow simultanious requests
    clearInterval(loopIntervalId);
    lastUsername = username;
    await getURL("addToQueue.php?username=" + username);
    loopIntervalId = setInterval(async () => {
        const json = await checkStatus(username);
        console.log(json.text)
        if (json.code === 2) {              //not in db
            clearInterval(loopIntervalId);
        } else if (json.code === 0) {       //waiting on queue
            clearInterval(loopIntervalId);
            fetchCsv(username);
        }
    }, 1000);
}
