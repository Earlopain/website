let currentlyOpenFile;
let currentlyOpenFileDir;
let firstCall = true;

let mimesTypes = {
    "text": ["text/", "application/x-csh", "application/json", "application/php", "application/x-sh", "application/xml"],
    "image": ["image/"],
    "audio": ["video/"],
    "video": ["audio/", "application/ogg"]
}

async function editFile(file, folderPath) {
    currentlyOpenFile = file;
    currentlyOpenFileDir = folderPath;
    const [fileContent, mimeType] = await serverRequestMimeType({ folder: folderPath, ids: file.index }, "getsinglefile");
    let editor = document.getElementById("editor");
    editor.innerHTML = "";
    switch (getMimeType(mimeType)) {
        case "text":
            let textarea = document.createElement("textarea");
            textarea.innerHTML = atob(fileContent);
            editor.appendChild(textarea);
            break;
        case "image":
            let img = document.createElement("img");
            img.src = "data:image/png;base64," + fileContent;
            img.classList.add("imageview");
            editor.appendChild(img);
            break;
        case "audio":
            break;
        case "video":
            break;
    }
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
