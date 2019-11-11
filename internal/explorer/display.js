window.addEventListener('DOMContentLoaded', () => {
    document.getElementById("currentfolder").addEventListener("keydown", event => {
        if (event.keyCode === 13) {
            getFolderContent();
        }
    });
    const currentUrl = new URL(location.href);
    const folder = currentUrl.searchParams.get("folder");
    document.getElementById("currentfolder").value = folder === null ? "/" : atob(folder);
    getFolderContent();
    registerTableSort();
});

function generateFileEntry(file) {
    let row = document.createElement("tr");
    row.id = "file" + file.index;
    let checkbox = document.createElement("input");
    checkbox.classList.add("checkbox");
    checkbox.type = "checkbox";
    row.appendChild(checkbox);
    let fileNameColumn = createTableColumn(file.fileName);
    addFolderEventListener(fileNameColumn, file);
    addFileEditEventListener(fileNameColumn, file);
    row.appendChild(fileNameColumn);
    row.appendChild(createTableColumn(file.isDir || file.fileName.startsWith(".") ? "": file.fileName.split(".").pop()));
    row.appendChild(createTableColumn(file.user));
    row.appendChild(createTableColumn(file.group));
    row.appendChild(createTableColumn(file.perms));
    row.appendChild(createTableColumn(file.isDir ? "" : file.size));
    row.appendChild(createTableColumn(file.isReadable));
    row.appendChild(createTableColumn(file.isWriteable));
    return row;
}

function addFolderEventListener(element, file) {
    if (file.isDir && file.isExecutable) {
        element.addEventListener("click", () => {
            const current = document.getElementById("currentfolder").value;
            let addition;
            if (current.slice(-1) === "/") {
                addition = file.fileName;
            }
            else {
                addition = "/" + file.fileName;
            }
            document.getElementById("currentfolder").value += addition;
            getFolderContent();
        })
    }
}

function addFileEditEventListener(element, file) {
    if (!file.isDir && file.isReadable) {
        element.addEventListener("click", () => {
            const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
            showFile(file, folderPath);
        })
    }
}

function createTableColumn(content) {
    let col = document.createElement("td");
    col.classList.add("column");
    col.innerHTML = content;
    return col;
}

function removeTrailingSlash(element) {
    element.value = element.value.replace(/\/$/, "");
    if (element.value === "")
        element.value = "/";
    return element.value;
}
