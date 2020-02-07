let commandInProgress = false;
let loadedFile;

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
            httpPOST({ "command": command, "link": document.getElementById("commandout").value });
            break;
        default:
            httpPOST({ "command": command });
            break;
    }
    document.getElementById("commandout").value = "";
}

function getFileFromServer(filePath) {
    if (commandInProgress) {
        console.log("already executing");
        return;
    }
    document.getElementById("commandout").value = "";
    showSubmitButton();
    loadedFile = filePath;
    commandInProgress = true;
    httpGET("executor.php?getfile=" + filePath);
}

function putFileOnServer() {
    httpPOST({ "savefile": loadedFile, "savefiledata": document.getElementById("commandout").value });
}

function hideSubmitButton() {
    document.getElementById("submitfile").style.display = "none";
}

function showSubmitButton() {
    document.getElementById("submitfile").style.display = "";
}


function requestOnProgress(event) {
    document.getElementById("commandout").value = event.target.responseText.substr(-50000);
    document.getElementById("commandout").scrollTop = 999999;
}

function httpGET(url) {
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", url, true); // false for synchronous request
    xmlHttp.onprogress = requestOnProgress;
    xmlHttp.onload = function () {
        commandInProgress = false;
    };
    xmlHttp.send(null);
}

function httpPOST(formDataJSON) {
    let xmlHttp = new XMLHttpRequest();
    let formData = new FormData();
    Object.keys(formDataJSON).forEach(key => {
        formData.append(key, formDataJSON[key])
    });
    xmlHttp.open("POST", "executor.php", true); // false for synchronous request
    xmlHttp.onprogress = requestOnProgress;
    xmlHttp.onload = function () {
        hideSubmitButton();
        commandInProgress = false;
    };
    xmlHttp.send(formData);
}
