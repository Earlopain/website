async function getFolderContent() {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
    response = JSON.parse(await serverRequest({ path: folderPath }, "getfolder"));
    response.entries = response.entries.sort((a, b) => {
        return a.fileName > b.fileName ? 1 : -1;
    });
    response.entries = response.entries.sort((a, b) => {
        if (a.isDir && !b.isDir)
            return -1;
        else if (!a.isDir && b.isDir)
            return 1;
        return 0;
    });
    let container = document.getElementById("filecontents");
    container.innerHTML = "";
    for (const entry of response.entries) {
        const element = generateFileEntry(entry);
        container.appendChild(element)
    }
}

async function downloadSelection() {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
    const files = document.getElementById("filecontents").childNodes;
    let postData = { folder: folderPath, ids: [] };
    for (const file of files) {
        if (file.childNodes[0].checked)
            postData.ids.push(file.id.substring(4));
    }
    const zipFile = await serverRequest(postData, "downloadselection", true);
    let a = document.createElement("a");
    let url = window.URL.createObjectURL(zipFile);
    a.href = url;
    a.download = "files.zip";
    a.click();
    window.URL.revokeObjectURL(url);
}

async function serverRequest(postData, type, isBinary = false) {
    postData.action = type;
    const result = await httpPOST("webInterface.php", postData, isBinary);
    return result;
}

function httpPOST(url, formDataJSON, isBinary = false) {
    return new Promise(resolve => {
        let xmlHttp = new XMLHttpRequest();
        if (isBinary) {
            xmlHttp.responseType = "arraybuffer";
        }
        let formData = new FormData();
        Object.keys(formDataJSON).forEach(key => {
            formData.append(key, formDataJSON[key])
        });
        xmlHttp.open("POST", url, true); // false for synchronous request
        xmlHttp.onload = event => {
            if (isBinary) {
                resolve(new Blob([event.target.response], {type: "octet/stream"}));
            }
            else {
                resolve(event.target.responseText);
            }
        };
        xmlHttp.send(formData);
    });
}
