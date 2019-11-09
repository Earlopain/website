window.addEventListener('DOMContentLoaded', () => {
    document.getElementById("currentfolder").addEventListener("keydown", event => {
        console.log(event);
        if (event.keyCode === 13) {
            getFolderContent();
        }
    });
    getFolderContent();
});

class OctalPermissions {
    constructor(octal) {
        this.user = octal.charAt(0);
        this.group = octal.charAt(1);
        this.other = octal.charAt(2);
    }

    userPerms() {
        return this.humanReadable(this.user);
    }

    humanReadable(value) {
        const isReadable = value % 4 === 0;
        const isWritable = value % 2 === 0;
        const isExecutable = value % 1 === 0;
        let result = "";
        result += isReadable ? "r" : "-";
        result += isWritable ? "w" : "-";
        result += isExecutable ? "x" : "-";
        return result;
    }
}

function generateFileEntry(file) {
    let row = document.createElement("tr");
    row.id = "file" + file.index;
    let checkbox = document.createElement("input");
    checkbox.classList.add("checkbox");
    checkbox.type = "checkbox";
    row.appendChild(checkbox);
    let fileName = createTableColumn(file.fileName, "filename");
    if (file.isDir) {
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
