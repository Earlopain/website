let commandInProgress = false;
let loadedFile;




document.getElementById("commandout");

function executeOnServer(command) {
    hideSubmitButton();
    if (commandInProgress) {
        console.log("already executing");
        return;
    }
    commandInProgress = true;
    switch (command) {
        case "deezerdl":
            httpGET("executor.php?command=" + command + "&link=" + document.getElementById("commandout").value);
            break;
        default:
            httpGET("executor.php?command=" + command);
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
    httpPOST("executor.php", { "savefile": loadedFile, "savefiledata": document.getElementById("commandout").value });
}

function hideSubmitButton() {
    document.getElementById("submitfile").style.display = "none";
}

function showSubmitButton() {
    document.getElementById("submitfile").style.display = "";
}

function httpGET(url) {
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", url, true); // false for synchronous request
    xmlHttp.onprogress = function (event) {
        document.getElementById("commandout").value = event.target.responseText;
        document.getElementById("commandout").scrollTop = 999999;
    };
    xmlHttp.onload = function () {
        commandInProgress = false;
    };
    xmlHttp.send(null);
}

function httpPOST(url, formDataJSON) {
    let xmlHttp = new XMLHttpRequest();
    let formData = new FormData();
    Object.keys(formDataJSON).forEach(key => {
        formData.append(key, formDataJSON[key])
    });
    xmlHttp.open("POST", url, true); // false for synchronous request

    xmlHttp.onload = function () {
        document.getElementById("commandout").value += "\nDone!";
        document.getElementById("commandout").scrollTop = 999999;
        hideSubmitButton();
        commandInProgress = false;
    };
    xmlHttp.send(formData);
}