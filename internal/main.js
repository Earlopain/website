let textarea;

window.addEventListener("DOMContentLoaded", () => {
    textarea = document.getElementById("textarea");

    let commandInProgress = false;

    for (const button of document.getElementsByTagName("button")) {
        button.addEventListener("click", async () => {
            if (commandInProgress) {
                console.log("already executing");
                return;
            }
            commandInProgress = true;

            const type = button.getAttribute("data-type");
            const data = button.getAttribute("data-extra");
            await execute(type, data);
            commandInProgress = false;
        });
    }

});

async function execute(type, data) {
    switch (type) {
        case "command":
            const textContent = textarea.value;
            textarea.value = "";
            hideSubmitButton();
            await executeOnServer(data, textContent);
            break;
        case "getfile":
            textarea.value = "";
            await getFileFromServer(data);
            document.getElementById("submitfile").setAttribute("filename", data);
            showSubmitButton();
            break;
        case "savefile":
            const filename = document.getElementById("submitfile").getAttribute("filename");
            hideSubmitButton();
            await putFileOnServer(filename, textarea.value);
            document.getElementById("submitfile").removeAttribute("filename");
            break;
    }
}

async function executeOnServer(command, extraData) {
    switch (command) {
        case "deezerdl":
        case "musicvideo":
        case "shortmovie":
        case "youtube":
        case "e621dl":
        case "e621replace":
            await httpPOST({ "command": command, "link": extraData });
            break;
        default:
            await httpPOST({ "command": command });
            break;
    }
}

async function getFileFromServer(filePath) {
    await httpGET("executor.php?getfile=" + filePath);
}

async function putFileOnServer(filename, data) {
    await httpPOST({ "savefile": filename, "savefiledata": data });
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
