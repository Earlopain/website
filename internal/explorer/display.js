window.addEventListener("DOMContentLoaded", () => {
    document.getElementById("currentfolder").addEventListener("keydown", event => {
        if (event.keyCode === 13) {
            getFolderContent();
        }
    });
    window.addEventListener("popstate", loadFromUrl);
    const slider = document.getElementById("filenameslider");
    slider.value = document.getElementById("tableheader").children[1].getBoundingClientRect().width;
    slider.addEventListener("input", event => {
        const newWidth = event.currentTarget.value + "px";
        const header = document.getElementById("tableheader").children[1];
        header.style.width = newWidth;
        const allEntries = document.getElementById("filecontents").children;
        for (const entry of allEntries) {
            entry.children[1].style.width = newWidth;
        }
    });
    loadFromUrl();
    registerTableSort();
});

function loadFromUrl() {
    const currentUrl = new URL(location.href);
    const folder = currentUrl.searchParams.get("folder");
    document.getElementById("currentfolder").value = folder === null ? "/" : atob(folder);
    getFolderContent(false);
}

async function displayCurrentFolder(pushToHistory = true) {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder").value);
    response = JSON.parse(await serverRequest("getdir", { path: folderPath }));
    if (response.folder.entries.length === 0) {
        document.getElementById("currentfolder").value = "/";
        displayCurrentFolder();
        return;
    }
    document.getElementById("loggedinas").innerHTML = response.username;
    if (pushToHistory) {
        const currentUrl = new URL(location.href);
        currentUrl.searchParams.set("folder", btoa(response.folder.currentFolder));
        window.history.pushState({}, null, currentUrl.href);
    }

    response.folder.entries = response.folder.entries.sort((a, b) => {
        if (a.isDir === b.isDir) {
            return a.fileName.localeCompare(b.fileName, undefined, { numeric: true, sensitivity: "base" });
        } else {
            return b.isDir - a.isDir;
        }
    });
    let container = document.getElementById("filecontents");
    container.innerHTML = "";
    if (response.folder.currentFolder !== "/") {
        const parentFolder = generateFileEntry(response.folder.parentFolder);
        container.appendChild(parentFolder);
    }
    for (const entry of response.folder.entries) {
        const element = generateFileEntry(entry);
        container.appendChild(element);
    }
}

function generateFileEntry(file) {
    let row = document.createElement("tr");
    row.id = "file" + file.index;
    let checkbox = document.createElement("input");
    checkbox.classList.add("checkbox");
    checkbox.type = "checkbox";
    row.appendChild(checkbox);
    let fileNameColumn = createTableColumn("filename", file.fileName);
    addFolderEventListener(fileNameColumn, file);
    addFileEditEventListener(fileNameColumn, file);
    row.appendChild(fileNameColumn);
    row.appendChild(createTableColumn("ext", file.isDir || file.fileName.startsWith(".") ? "" : file.fileName.split(".").pop()));
    row.appendChild(createTableColumn("size", file.isDir ? "" : file.size));
    row.appendChild(createTableColumn("user", file.user));
    row.appendChild(createTableColumn("group", file.group));
    row.appendChild(createTableColumn("perms", file.perms));
    row.appendChild(createTableColumn("readable", file.isDir ? file.isExecutable : file.isReadable));
    row.appendChild(createTableColumn("writeable", file.isDir ? file.isExecutable && file.isWriteable : file.isWriteable));
    return row;
}

function addFolderEventListener(element, file) {
    if (file.isDir && file.isExecutable) {
        element.addEventListener("click", () => {
            const current = document.getElementById("currentfolder").value;
            let addition;
            if (current.slice(-1) === "/") {
                addition = file.fileName;
            } else {
                addition = "/" + file.fileName;
            }
            if (addition === "/..") {
                const value = document.getElementById("currentfolder").value;
                const splitted = value.split("/");
                splitted.pop();
                document.getElementById("currentfolder").value = splitted.join("/");
            } else {
                document.getElementById("currentfolder").value += addition;
            }
            getFolderContent();
        });
    }
}

function addFileEditEventListener(element, file) {
    if (!file.isDir && file.isReadable) {
        element.addEventListener("click", () => {
            const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
            showFile(file, folderPath);
        });
    }
}

function createTableColumn(type, content) {
    let col = document.createElement("td");
    col.classList.add(type);
    col.innerHTML = content;
    return col;
}

function removeTrailingSlash(element) {
    element.value = element.value.replace(/\/$/, "");
    if (element.value === "") {
        element.value = "/";
    }
    return element.value;
}
