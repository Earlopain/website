async function downloadSelection() {
    const folderPath = tableView.getCurrentFolderPath();
    const files = document.getElementById("filecontents").childNodes;
    let ids = [];
    let nonDownloadable = [];
    for (const file of files) {
        if (file.childNodes[0].checked && file.childNodes[7].textContent === "true") {
            ids.push(file.id.substring(4));
        } else if (file.childNodes[0].checked) {
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
    let input = document.createElement("input");
    input.type = "text";
    input.name = "data";
    input.value = btoa(JSON.stringify(postData));
    form.appendChild(input);
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
        tableView.displayCurrentFolder(false);
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
        formData.append("data", btoa(JSON.stringify(formDataJSON)));
        xmlHttp.open("POST", url, true);
        xmlHttp.onload = event => {
            resolve(event.target.responseText);
        };
        xmlHttp.send(formData);
    });
}
