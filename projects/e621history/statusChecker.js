async function checkStatus(username) {
    const url = "userStatus.php?username=" + username;
    const json = JSON.parse(await getURL(url));
    console.log(json);
}
