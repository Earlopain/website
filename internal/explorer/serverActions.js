async function getFolderContent() {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
    response = JSON.parse(await serverRequest({ path: folderPath }, "getfolder"));
    document.getElementById("currentfolder").value = response.currentFolder;
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
    if (response.currentFolder !== "/") {
        let fakeFile = { fileName: "..", group: "", user: "", isDir: true, perms: "", size: -1, isReadable: "", isWriteable: "" };
        container.appendChild(generateFileEntry(fakeFile));
    }

    for (const entry of response.entries) {
        const element = generateFileEntry(entry);
        container.appendChild(element)
    }
}

async function downloadSelection() {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
    const files = document.getElementById("filecontents").childNodes;
    let ids = [];
    for (const file of files) {
        if (file.childNodes[0].checked)
            ids.push(file.id.substring(4));
    }
    postDownload({ action: "downloadselection", folder: folderPath, ids: ids.join(",") });
}

function postDownload(postData) {
    console.log(postData);
    let form = document.createElement("form");
    for (const name of Object.keys(postData)) {
        const value = postData[name];
        let input = document.createElement("input");
        input.type = "text";
        input.name = name;
        input.value = value;
        form.appendChild(input);
    }
    form.method = "post";
    form.action = "webInterface.php";
    form.id = "tempform";

    document.body.appendChild(form);
    document.getElementById("tempform").submit();
    document.getElementById("tempform").remove();
}

async function serverRequest(postData, type) {
    postData.action = type;
    const result = await httpPOST("webInterface.php", postData);
    return result;
}

function httpPOST(url, formDataJSON) {
    return new Promise(resolve => {
        let xmlHttp = new XMLHttpRequest();
        let formData = new FormData();
        Object.keys(formDataJSON).forEach(key => {
            formData.append(key, formDataJSON[key])
        });
        xmlHttp.open("POST", url, true); // false for synchronous request
        xmlHttp.onload = event => {
            resolve(event.target.responseText);
        };
        xmlHttp.send(formData);
    });
}
