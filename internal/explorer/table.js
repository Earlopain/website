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

    let entriesCopy = [...tableView.tableElements].slice();
    tableView.removeAllEntires();
    const dotdot = tableView.getCurrentFolderPath() !== "/" ? entriesCopy.shift() : undefined;
    switch (sortType[index]) {
        case "string":
            entriesCopy = entriesCopy.sort(stringSort.bind(index));
            break;
        case "size":
            entriesCopy = entriesCopy.sort(sizeSort.bind(index));
        default:
            break;
    }
    let previousValue = currentOrder[index];
    currentOrder = Array(sortType.length).fill(1);
    currentOrder[index] = previousValue * -1;
    let newTableBody = document.createElement("tbody");
    if (dotdot !== undefined) {
        newTableBody.appendChild(dotdot);
    }

    for (const entry of entriesCopy) {
        newTableBody.appendChild(entry);
    }
    document.querySelector("#" + tableView.tableElementId).appendChild(newTableBody);
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

document.addEventListener('keydown', (event) => {
    if (tableView.tableElements.length === 0) {
        return;
    }
    const currentSelected = document.querySelector(".selectedtablerow");
    const key = event.key;
    if (key === "ArrowUp") {
        setActive(currentSelected.previousElementSibling, currentSelected);
    }
    else if (key === "ArrowDown") {
        setActive(currentSelected.nextSibling, currentSelected);
    }
    else if (key === "Enter") {
        let currentFile;
        for (const file of tableView.serverResponse.folder.entries) {
            if (file.index === parseInt(currentSelected.id.substring(4))) {
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
});

function setFirstEntryActive() {
    const first = document.querySelector(`#${tableView.tableElementId} tbody tr:first-child`);
    first.classList.add("selectedtablerow");
}

function setActive(newElement, oldElement) {
    if (newElement !== null) {
        oldElement.classList.remove("selectedtablerow");
        newElement.classList.add("selectedtablerow");
    }
}
