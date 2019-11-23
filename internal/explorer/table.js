let currentOrder = [];
let sortType = ["none", "string", "string", "size", "string", "string", "string", "string", "string"];

function registerTableSort() {
    for (const header of tableView.getHeaders()) {
        header.addEventListener("click", () => {
            const sorted = sortColumn(header.cellIndex);
            tableView.setTableEntries(sorted);
        });
    }
    currentOrder = Array(sortType.length).fill(1);
}

function sortColumn(index) {
    if (sortType[index] === "none") {
        return;
    }

    let entriesCopy = [...tableView.tableElements].slice();
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
    if (dotdot !== undefined) {
        entriesCopy.unshift(dotdot);
    }
    return entriesCopy;
}

function convertToBytes(input) {
    const units = ["B", "KB", "MB", "GB", "TB"];
    const split = input.split(" ");
    const index = units.indexOf(split[1]);
    return split[0] * Math.pow(1024, index);
}

function stringSort(a, b) {
    return folderSort(a, b, () => {
        a = a.children[this].innerText;
        b = b.children[this].innerText;
        return currentOrder[this] * a.localeCompare(b, undefined, { numeric: true, sensitivity: "base" });
    });
}

function sizeSort(a, b) {
    return folderSort(a, b, () => {
        a = convertToBytes(a.children[this].innerText);
        b = convertToBytes(b.children[this].innerText);
        return currentOrder[this] * (a - b);
    });
}

function folderSort(a, b, callback) {
    const aIsDir = a.children[3].innerText === "";
    const bIsDir = b.children[3].innerText === "";
    if (aIsDir === bIsDir) {
        return callback();
    }
    else {
        return bIsDir - aIsDir;
    }
}

document.addEventListener('keydown', (event) => {
    if (tableView.tableElements.length === 0) {
        return;
    }
    const currentSelected = document.querySelector(".selectedtablerow");
    const key = event.key;
    if (key === "ArrowUp") {
        event.preventDefault();
        setActive(currentSelected.previousElementSibling, currentSelected);
    }
    else if (key === "ArrowDown") {
        event.preventDefault();
        setActive(currentSelected.nextSibling, currentSelected);
    }
    else if (key === "Enter") {
        event.preventDefault();
        let currentFile;
        for (const file of tableView.serverResponse.folder.entries) {
            if (file.index === parseInt(currentSelected.id.substring(4))) {
                currentFile = file;
                break;
            }
        }
        if (currentFile === undefined) {
            tableView.addStringToCurrentFolderPath("..");
            tableView.displayCurrentFolder();
        }
        else if (currentFile.isDir && currentFile.isExecutable && currentFile.isReadable) {
            tableView.addStringToCurrentFolderPath(currentFile.fileName);
            tableView.displayCurrentFolder();
        } else if (!currentFile.isDir && currentFile.isReadable) {
            editor.showFile(currentFile);
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
