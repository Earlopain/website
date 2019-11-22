let currentlyOpenFile;
let currentlyOpenFileDir;
let lastClickedFile;
let lastClickedFileDir;

let mimesTypes = {
    textarea: ["text/", "application/x-csh", "application/json", "application/php", "application/x-sh", "application/xml"],
    img: ["image/"],
    audio: ["audio/"],
    video: ["video/", "application/ogg"]
};

async function showFile(file, folderPath) {
    if ((currentlyOpenFile === file && currentlyOpenFileDir === folderPath) || (lastClickedFile === file && lastClickedFileDir === folderPath)) {
        return;
    }
    lastClickedFile = file;
    lastClickedFileDir = folderPath;
    document.getElementById("currentlyviewing").innerHTML = ", viewing: " + file.fileName;
    let editor = document.getElementById("editor");
    const json = { action: "getsinglefile", folder: folderPath, id: file.index };
    const mimeType = await serverRequest("getsinglefile", { folder: folderPath, id: file.index, mimeonly: "true" });
    const elementType = getMimeType(mimeType);
    if (elementType === "unsupported") {
        return;
    }
    editor.innerHTML = "";
    let mediaElement = document.createElement(elementType);
    if (elementType === "textarea") {
        const data = await serverRequest("getsinglefile", json);
        mediaElement.innerHTML = data;
    } else {
        const url = "previlegeWrapper.php?data=" + encodeURI(btoa(JSON.stringify(json)));
        mediaElement.controls = true;
        mediaElement.src = url;
        mediaElement.onload = function () {
            if (mediaElement.height > mediaElement.width) {
                mediaElement.style.height = "calc(100% - var(--editor-margin) * 2)";
                mediaElement.style.width = "auto";
            }
        };
    }
    editor.appendChild(mediaElement);
    currentlyOpenFile = file;
    currentlyOpenFileDir = folderPath;
}

function getMimeType(mime) {
    for (const mimeString of Object.keys(mimesTypes)) {
        const entries = mimesTypes[mimeString].slice();
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
