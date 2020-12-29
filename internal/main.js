window.addEventListener("DOMContentLoaded", () => {
    new Executor();
});

class Executor {
    constructor() {
        this.textarea = document.getElementById("textarea");
        this.submitButton = document.getElementById("submitfile");
        this.commandInProgress = false;
        this.fileToSave = undefined;
        this.setupEventListeners();
    }

    setupEventListeners() {
        for (const button of document.getElementsByTagName("button")) {
            button.addEventListener("click", async () => {
                if (this.commandInProgress) {
                    console.log("already executing");
                    return;
                }
                this.commandInProgress = true;

                const type = button.getAttribute("data-type");
                const data = button.getAttribute("data-extra");
                await this.execute(type, data);
                this.commandInProgress = false;
            });
        }
    }

    async execute(type, data) {
        switch (type) {
            case "command":
                const textContent = this.textarea.value;
                this.textarea.value = "";
                hideElement(this.submitButton);
                await this.executeOnServer(data, textContent);
                break;
            case "getfile":
                this.textarea.value = "";
                await this.getFileFromServer(data);
                this.fileToSave = data;
                showElement(this.submitButton);
                break;
            case "savefile":
                if (this.fileToSave === undefined) {
                    console.log("Something went wrong");
                    return;
                }
                await this.putFileOnServer(this.fileToSave, textarea.value);
                this.fileToSave = undefined;
                break;
        }
    }

    async executeOnServer(command, extraData) {
        switch (command) {
            case "deezerdl":
            case "musicvideo":
            case "shortmovie":
            case "youtube":
            case "e621dl":
            case "e621replace":
                await httpPOST({ "command": command, "link": extraData }, this.readerCallback);
                break;
            default:
                await httpPOST({ "command": command }, this.readerCallback);
                break;
        }
    }

    async getFileFromServer(filePath) {
        await httpGET("executor.php?getfile=" + filePath, this.readerCallback);
    }

    async putFileOnServer(filename, data) {
        await httpPOST({ "savefile": filename, "savefiledata": data }, this.readerCallback);
    }

    readerCallback(text) {
        textarea.value += text;
        textarea.scrollTop = textarea.scrollHeight;
    }
}

function hideElement(e) {
    e.style.display = "none";
}

function showElement(e) {
    e.style.display = "";
}

async function httpGET(url, callback = function () { }) {
    const request = await fetch(url);
    await handleReader(request.body.getReader(), callback);
}

async function httpPOST(formDataJSON, callback = function () { }) {
    const request = await fetch("executor.php", {
        method: "POST",
        body: JSON.stringify(formDataJSON)
    });
    await handleReader(request.body.getReader(), callback);
}

async function handleReader(reader, callback = function () { }) {
    const decoder = new TextDecoder("utf-8");
    while (true) {
        const read = await reader.read();
        callback(decoder.decode(read.value));
        if (read.done) {
            break;
        }
    }
}
