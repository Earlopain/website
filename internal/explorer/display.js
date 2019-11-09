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

async function getFolderContent() {
    const folderPath = removeTrailingSlash(document.getElementById("currentfolder"));
    response = JSON.parse(await httpPOST("getfolderinfo.php", { path: folderPath }));
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

function generateFileEntry(file) {
    let row = document.createElement("tr");
    let checkbox = document.createElement("input");
    checkbox.classList.add("checkbox");
    checkbox.type = "checkbox";
    row.appendChild(checkbox);
    let fileName = createTableColumn(file.fileName);
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
    fileName.classList.add("filename");
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
