class Editor {
    constructor(elementContainerId, currentlyViewingId) {
        this.elementId = elementContainerId;
        this.currentlyViewingId = currentlyViewingId;
        this.currentlyOpenFile = "";
        this.currentlyOpenFileDir = "";
        this.lastClickedFile = "";
        this.lastClickedFileDir = "";
        this.mimesTypes = {
            textarea: ["text/", "application/x-csh", "application/json", "application/php", "application/x-sh", "application/xml"],
            img: ["image/"],
            audio: ["audio/"],
            video: ["video/", "application/ogg"]
        };
    }

    async showFile(file) {
        const folderPath = manager.tableView.getCurrentFolderPath();
        if ((this.currentlyOpenFile === file && this.currentlyOpenFileDir === folderPath) ||
            (this.lastClickedFile === file && this.lastClickedFileDir === folderPath)) {
            return;
        }
        this.lastClickedFile = file;
        this.lastClickedFileDir = folderPath;
        let editor = document.getElementById(this.elementId);
        const json = { action: "getsinglefile", folder: folderPath, id: file.index };
        const mimeType = await serverRequest("getsinglefile", { folder: folderPath, id: file.index, mimeonly: "true" });
        const elementType = this.getMimeType(mimeType);
        if (elementType === "unsupported") {
            return;
        }
        document.getElementById(this.currentlyViewingId).innerHTML = ", viewing: " + file.fileName;
        editor.innerHTML = "";
        let mediaElement = document.createElement(elementType);
        if (elementType === "textarea") {
            const data = await serverRequest("getsinglefile", json);
            mediaElement.innerHTML = data;
        } else {
            const url = "previlegeWrapper.php?data=" + encodeURI(btoa(JSON.stringify(json)));
            mediaElement.controls = true;
            mediaElement.src = url;
            mediaElement.addEventListener("load", () => {
                if (mediaElement.height > mediaElement.width) {
                    mediaElement.style.height = "calc(100% - var(--editor-margin) * 2)";
                    mediaElement.style.width = "auto";
                }
            });
        }
        editor.appendChild(mediaElement);
        this.currentlyOpenFile = file;
        this.currentlyOpenFileDir = folderPath;
    }

    getMimeType(mime) {
        for (const mimeString of Object.keys(this.mimesTypes)) {
            const entries = this.mimesTypes[mimeString].slice();
            const firstEntry = entries.shift();
            if (mime.startsWith(firstEntry)) {
                return mimeString;
            }
            for (const entry of entries) {
                if (mime === entry) {
                    return mimeString;
                }
            }
        }
        return "unsupported";
    }
}
