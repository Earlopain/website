let commandInProgress = false;
let loadedFile;

let textarea;
window.addEventListener("DOMContentLoaded" , () => {
    textarea = document.getElementById("textarea");
});

function executeOnServer(command) {
    hideSubmitButton();
    if (commandInProgress) {
        console.log("already executing");
        return;
    }
    commandInProgress = true;
    switch (command) {
        case "deezerdl":
        case "musicvideo":
        case "shortmovie":
        case "youtube":
        case "e621dl":
        case "e621replace":
            httpPOST({ "command": command, "link": textarea.value });
            break;
        default:
            httpPOST({ "command": command });
            break;
    }
    textarea.value = "";
}

function getFileFromServer(filePath) {
    if (commandInProgress) {
        console.log("already executing");
        return;
    }
    textarea.value = "";
    showSubmitButton();
    loadedFile = filePath;
    commandInProgress = true;
    httpGET("executor.php?getfile=" + filePath);
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


function requestOnProgress(event) {
    textarea.value = event.target.responseText.substr(-50000);
    textarea.scrollTop = 999999;
}

function httpGET(url) {
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", url, true); // false for synchronous request
    xmlHttp.addEventListener("progress", requestOnProgress);
    xmlHttp.addEventListener("load", () => {
        commandInProgress = false;
    });
    xmlHttp.send(null);
}

function httpPOST(formDataJSON) {
    let xmlHttp = new XMLHttpRequest();
    let formData = new FormData();
    Object.keys(formDataJSON).forEach(key => {
        formData.append(key, formDataJSON[key])
    });
    xmlHttp.open("POST", "executor.php", true); // false for synchronous request
    xmlHttp.addEventListener("progress", requestOnProgress);
    xmlHttp.addEventListener("load", () => {
        hideSubmitButton();
        commandInProgress = false;
    });
    xmlHttp.send(formData);
}
