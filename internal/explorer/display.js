window.addEventListener("DOMContentLoaded", () => {
    document.getElementById("currentfolder").addEventListener("keydown", event => {
        if (event.keyCode === 13) {
            getFolderContent();
        }
    });
    window.addEventListener("popstate", loadFromUrl);
    loadFromUrl();
    registerTableSort();
});

function loadFromUrl() {
    const currentUrl = new URL(location.href);
    const folder = currentUrl.searchParams.get("folder");
    document.getElementById("currentfolder").value = folder === null ? "/" : atob(folder);
    getFolderContent(false);
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
    row.appendChild(createTableColumn("user", file.user));
    row.appendChild(createTableColumn("group", file.group));
    row.appendChild(createTableColumn("perms", file.perms));
    row.appendChild(createTableColumn("size", file.isDir ? "" : file.size));
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
