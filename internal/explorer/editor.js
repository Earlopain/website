let currentlyOpenFile;
let currentlyOpenFileDir;
let firstCall = true;

let mimesTypes = {
    "text": ["text/", "application/x-csh", "application/json", "application/php", "application/x-sh", "application/xml"],
    "image": ["image/"],
    "audio": ["audio/"],
    "video": ["video/", "application/ogg"]
}

async function editFile(file, folderPath) {
    currentlyOpenFile = file;
    currentlyOpenFileDir = folderPath;
    const folderBase64 = encodeURI(btoa(folderPath));
    const url = "fileProxy.php?folder=" + folderBase64 + "&id=" + file.index;
    const mimeType = await httpHEAD(url);
    let editor = document.getElementById("editor");
    editor.innerHTML = "";
    switch (getMimeType(mimeType)) {
        case "text":
            let textarea = document.createElement("textarea");
            const data = await httpPOST(url);
            textarea.innerHTML = data;
            editor.appendChild(textarea);
            break;
        case "image":
            let img = document.createElement("img");
            img.src = url;
            editor.appendChild(img);
            break;
        case "audio":
            let audio = document.createElement("audio");
            audio.src = url;
            audio.controls = true;
            editor.appendChild(audio);
            break;
        case "video":
            let video = document.createElement("video");
            video.controls = true;
            let source = document.createElement("source");
            source.src = url;
            video.appendChild(source)
            editor.appendChild(video);
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
