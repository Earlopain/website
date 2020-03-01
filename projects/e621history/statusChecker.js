async function checkStatus(username) {
    const url = "userStatus.php?username=" + username;
    return JSON.parse(await getURL(url));
}

let lastUsername;
let lastInvervalId;

async function startLoop() {
    const username = document.getElementById("username").value;
    if (username === lastUsername) {
        infoMessage("already processing", "info");
        return;
    }
    clearInterval(lastInvervalId);
    lastUsername = username;
    await getURL("addToQueue.php?username=" + username);
    const callback = async () => {
        const json = await checkStatus(username);
        console.log(json.text);
        if (json.code === 0) {       //waiting on queue
            fetchCsv(username);
        }
        return json.code;
    };
    let shouldRetry = true;
    lastInvervalId = setInterval(async () => {
        if (!shouldRetry) {
            return;
        }
        shouldRetry = false;
        const resultCode = await callback();
        if (resultCode <= 0) {
            clearInterval(lastInvervalId);
        }
        shouldRetry = true;

    }, 2500);


}
