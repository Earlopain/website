let commandInProgress = false;
let loadedFile;

let textarea;
window.addEventListener("DOMContentLoaded", () => {
    textarea = document.getElementById("textarea");
});

async function executeOnServer(command) {
    if (commandInProgress) {
        console.log("already executing");
        return;
    }
    commandInProgress = true;
    textarea.value = "";
    switch (command) {
        case "deezerdl":
        case "musicvideo":
        case "shortmovie":
        case "youtube":
        case "e621dl":
        case "e621replace":
            await httpPOST({ "command": command, "link": textarea.value });
            break;
        default:
            await httpPOST({ "command": command });
            break;
    }
    commandInProgress = false;
}

async function getFileFromServer(filePath) {
    if (commandInProgress) {
        console.log("already executing");
        return;
    }
    commandInProgress = true;
    textarea.value = "";
    loadedFile = filePath;
    await httpGET("executor.php?getfile=" + filePath);
    showSubmitButton();
    commandInProgress = false;
}

function putFileOnServer() {
    httpPOST({ "savefile": loadedFile, "savefiledata": textarea.value });
}

function hideSubmitButton() {
    document.getElementById("submitfile").style.display = "none";
}

function showSubmitButton() {
    document.getElementById("submitfile").style.display = "";
}

async function httpGET(url) {
    const request = await fetch(url);
    await handleReader(request.body.getReader());
}

async function httpPOST(formDataJSON) {
    const request = await fetch("executor.php", {
        method: "POST",
        body: JSON.stringify(formDataJSON)
    });
    await handleReader(request.body.getReader());
}


async function handleReader(reader, callback) {
    const decoder = new TextDecoder("utf-8");
    while (true) {
        const read = await reader.read();
        textarea.value += decoder.decode(read.value);
        textarea.scrollTop = textarea.scrollHeight;
        if (read.done) {
            break;
        }
    }
}
