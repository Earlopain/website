async function checkStatus(username) {
    const url = "userStatus.php?username=" + username;
    return JSON.parse(await getURL(url));
}

async function startLoop() {
    const username = document.getElementById("username").value;
    await getURL("addToQueue.php?username=" + username);
    let intervalId = setInterval(async () => {
        const json = await checkStatus(username);
        console.log(json.text)
        if (json.code === 2) {              //not in db
            clearInterval(intervalId);
        } else if (json.code === 0) {       //waiting on queue
            clearInterval(intervalId);
            fetchCsv(username);
        }
    }, 1000);
}
