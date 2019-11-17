async function getFolderContent(pushToHistory = true) {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
    response = JSON.parse(await serverRequest("getdir", { path: folderPath }));
    if (response.folder.entries.length === 0) {
        document.getElementById("currentfolder").value = "/";
        getFolderContent();
        return;
    }
    document.getElementById("currentfolder").value = response.folder.currentFolder;
    document.getElementById("loggedinas").innerHTML = response.username;
    if (pushToHistory) {
        const currentUrl = new URL(location.href);
        currentUrl.searchParams.set("folder", btoa(response.folder.currentFolder));
        window.history.pushState({}, null, currentUrl.href);
    }

    response.folder.entries = response.folder.entries.sort((a, b) => {
        if ((a.isDir === b.isDir)) {
            return a.fileName.localeCompare(b.fileName, undefined, { numeric: true, sensitivity: "base" });
        } else {
            return b.isDir - a.isDir;
        }
    });
    let container = document.getElementById("filecontents");
    container.innerHTML = "";
    if (response.folder.currentFolder !== "/") {
        const parentFolder = generateFileEntry(response.folder.parentFolder)
        container.appendChild(parentFolder);
    }
    for (const entry of response.folder.entries) {
        const element = generateFileEntry(entry);
        container.appendChild(element)
    }
}

async function downloadSelection() {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
    const files = document.getElementById("filecontents").childNodes;
    let ids = [];
    let nonDownloadable = [];
    for (const file of files) {
        if (file.childNodes[0].checked && file.childNodes[7].textContent === "true") {
            ids.push(file.id.substring(4));
        }
        else if (file.childNodes[0].checked) {
            nonDownloadable.push(file.childNodes[1].textContent);
        }
    }
    if (ids.length > 0) {
        postDownload({ action: "zipselection", folder: folderPath, ids: ids.join(",") });
    }
    if (nonDownloadable.length > 0) {
        alert("These items are not downloadable because of permissions\n\n" + nonDownloadable.join("\n"));
    }
}

function postDownload(postData) {
    let form = document.createElement("form");
    for (const name of Object.keys(postData)) {
        const value = postData[name];
        let input = document.createElement("input");
        input.type = "text";
        input.name = name;
        input.value = btoa(value);
        form.appendChild(input);
    }
    form.method = "post";
    form.action = "previlegeWrapper.php";
    form.id = "tempform";

    document.body.appendChild(form);
    document.getElementById("tempform").submit();
    document.getElementById("tempform").remove();
}

async function login(successCallback = () => { }) {
    const user = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    const status = await serverRequest("validatePassword", { user: user, password: password });
    if (status !== "false") {
        successCallback();
    } else {
        alert("Wrong credentials");
    }
}

function loginAndGotoIndex() {
    login(() => {
        location.href = location.href.split("/").slice(0, -1).join("/");
    });
}

function loginAndReloadFolder() {
    login(() => {
        getFolderContent(false);
    });
}

async function serverRequest(type, postData) {
    postData.action = type;
    const result = await httpPOST("previlegeWrapper.php", postData);
    return result;
}

function httpPOST(url, formDataJSON = {}) {
    return new Promise(resolve => {
        let xmlHttp = new XMLHttpRequest();
        let formData = new FormData();
        Object.keys(formDataJSON).forEach(key => {
            formData.append(key, btoa(formDataJSON[key]))
        });
        xmlHttp.open("POST", url, true);
        xmlHttp.onload = event => {
            resolve(event.target.responseText);
        };
        xmlHttp.send(formData);
    });
}
