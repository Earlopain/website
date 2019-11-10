let currentlyOpenFile;
let currentlyOpenFileDir;
let firstCall = true;

let mimesTypes = {
    "textarea": ["text/", "application/x-csh", "application/json", "application/php", "application/x-sh", "application/xml"],
    "img": ["image/"],
    "audio": ["audio/"],
    "video": ["video/", "application/ogg"]
}

async function showFile(file, folderPath) {
    currentlyOpenFile = file;
    currentlyOpenFileDir = folderPath;
    const folderBase64 = encodeURI(btoa(folderPath));
    const url = "fileProxy.php?folder=" + folderBase64 + "&id=" + file.index;
    const mimeType = await httpHEAD(url);
    let editor = document.getElementById("editor");
    editor.innerHTML = "";
    const elementType = getMimeType(mimeType);
    let mediaElement = document.createElement(elementType);
    if (elementType === "textarea") {
        const data = await httpPOST(url);
        mediaElement.innerHTML = data;
    }
    else {
        mediaElement.controls = true;
        mediaElement.src = url;
        mediaElement.onload = function () {
            if (mediaElement.height > mediaElement.width) {
                mediaElement.style.height = '100%';
                mediaElement.style.width = 'auto';
            }
        }
    }
    editor.appendChild(mediaElement);
}

function getMimeType(mime) {
    for (const mimeString of Object.keys(mimesTypes)) {
        const entries = mimesTypes[mimeString];
        for (const entry of entries) {
            if (mime.includes(entry))
                return mimeString;
        }
    }
    return "unsupported";
}
