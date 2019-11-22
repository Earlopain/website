let currentOrder = [];
let sortType = ["none", "string", "string", "size", "string", "string", "string", "string", "string"];

function registerTableSort() {
    for (const header of tableView.getHeaders()) {
        header.addEventListener("click", () => sortColum(header.cellIndex));
    }
    currentOrder = Array(sortType.length).fill(-1);
}

function sortColum(index) {
    if (sortType[index] === "none") {
        return;
    }
    let container = document.getElementById("filecontents");
    let allEntries = container.children;

    allEntries = [...allEntries].slice();
    container.innerHTML = "";
    const dotdot = tableView.getCurrentFolderPath() !== "/" ? allEntries.shift() : undefined;
    switch (sortType[index]) {
        case "string":
            allEntries = allEntries.sort(stringSort.bind(index));
            break;
        case "size":
            allEntries = allEntries.sort(sizeSort.bind(index));
        default:
            break;
    }
    let previousValue = currentOrder[index];
    currentOrder = Array(sortType.length).fill(1);
    currentOrder[index] = previousValue * -1;
    if (dotdot !== undefined) {
        container.appendChild(dotdot);
    }

    for (const entry of allEntries) {
        container.appendChild(entry);
    }
}

function convertToBytes(input) {
    const units = ["B", "KB", "MB", "GB", "TB"];
    const split = input.split(" ");
    const index = units.indexOf(split[1]);
    return split[0] * Math.pow(1024, index);
}

function stringSort(a, b) {
    a = a.children[this].innerText;
    b = b.children[this].innerText;
    return currentOrder[this] * a.localeCompare(b, undefined, { numeric: true, sensitivity: "base" });
}

function sizeSort(a, b) {
    a = convertToBytes(a.children[this].innerText);
    b = convertToBytes(b.children[this].innerText);
    return currentOrder[this] * (a - b);
}

let currentRowIndex = 0;

document.addEventListener('keydown', (event) => {
    if (tableView.tableElements.length === 0) {
        return;
    }
    getCurrentSelectedRow().classList.remove("selectedtablerow");
    const key = event.key;
    if (key === "ArrowUp") {
        if (currentRowIndex > 0) {
            currentRowIndex--;
        }
    }
    else if (key === "ArrowDown") {
        if (currentRowIndex < tableView.tableElements.length - 1) {
            currentRowIndex++;
        }
    }
    else if (key === "Enter") {
        let currentFile;
        for (const file of tableView.serverResponse.folder.entries) {
            if (file.index === parseInt(getCurrentSelectedRow().id.substring(4))) {
                currentFile = file;
                break;
            }
        }
        if(currentFile === undefined) {
            tableView.addStringToCurrentFolderPath("..");
            tableView.displayCurrentFolder();
        }
        else if (currentFile.isDir) {
            tableView.addStringToCurrentFolderPath(currentFile.fileName);
            tableView.displayCurrentFolder();
        } else {
            editor.showFile(currentFile, tableView.getCurrentFolderPath());
        }
    }
    getCurrentSelectedRow().classList.add("selectedtablerow");
});

function getCurrentSelectedRow() {
    return tableView.tableElements[currentRowIndex];
}

function setCurrentRows() {
    tableView.tableElements[0].classList.add("selectedtablerow");
    currentRowIndex = 0;
}
