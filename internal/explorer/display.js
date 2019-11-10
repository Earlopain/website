window.addEventListener('DOMContentLoaded', () => {
    document.getElementById("currentfolder").addEventListener("keydown", event => {
        if (event.keyCode === 13) {
            getFolderContent();
        }
    });
    getFolderContent();
});


function generateFileEntry(file) {
    let row = document.createElement("tr");
    row.id = "file" + file.index;
    let checkbox = document.createElement("input");
    checkbox.classList.add("checkbox");
    checkbox.type = "checkbox";
    row.appendChild(checkbox);
    let fileName = createTableColumn(file.fileName, "filename");
    if (file.isDir && file.isExecutable) {
        fileName.addEventListener("click", () => {
            const current = document.getElementById("currentfolder").value;
            let addition;
            if (current.slice(-1) === "/") {
                addition = file.fileName
            }
            else {
                addition = "/" + file.fileName
            }
            document.getElementById("currentfolder").value += addition;
            getFolderContent();
        })
    }
    row.appendChild(fileName);
    row.appendChild(fileName);
    row.appendChild(createTableColumn(file.user, "user"));
    row.appendChild(createTableColumn(file.group, "group"));
    row.appendChild(createTableColumn(file.perms, "perms"));
    row.appendChild(createTableColumn(file.isDir ? "" : file.size, "size"));
    row.appendChild(createTableColumn(file.isReadable), "readable");
    row.appendChild(createTableColumn(file.isWriteable), "writeable");

    return row;
}

function createTableColumn(content, className) {
    let col = document.createElement("td");
    col.classList.add(className);
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
