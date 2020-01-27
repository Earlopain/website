class TableSort {
    constructor(sortType) {
        this.sortType = sortType;
        this.currentOrder = Array(this.sortType.length).fill(1);
        for (const header of manager.tableView.getHeaders()) {
            header.addEventListener("click", () => {
                const sorted = this.sortColumn(header.cellIndex);
                manager.tableView.setTableEntries(sorted);
                let previousValue = this.currentOrder[header.cellIndex];
                this.currentOrder = Array(this.sortType.length).fill(1);
                this.currentOrder[header.cellIndex] = previousValue * -1;
            });
        }
        this.addEventListener();
    }

    sortColumn(index) {
        if (this.sortType[index] === "none") {
            return;
        }
        let entriesCopy = [...manager.tableView.tableElements].slice();
        const dotdot = manager.tableView.getCurrentFolderPath() !== "/" ? entriesCopy.shift() : undefined;
        switch (this.sortType[index]) {
            case "string":
                const hexValidator = /^[0-9A-Fa-f]*$/;
                let doNumeric = false;
                for (const element of entriesCopy.slice(0, 10)) {
                    if (!hexValidator.test(element.children[1].innerText.split(".")[0])) {
                        doNumeric = true;
                        break;
                    }
                }
                entriesCopy = entriesCopy.sort(this.folderSort(index, this.stringSort, doNumeric));
                break;
            case "size":
                entriesCopy = entriesCopy.sort(this.folderSort(index, this.sizeSort));
            default:
                break;
        }
        if (dotdot !== undefined) {
            entriesCopy.unshift(dotdot);
        }
        return entriesCopy;
    }
    convertToBytes(input) {
        const units = ["B", "KB", "MB", "GB", "TB"];
        const split = input.split(" ");
        const index = units.indexOf(split[1]);
        return split[0] * Math.pow(1024, index);
    }

    stringSort(a, b, sortIndex, doNumeric) {
        a = a.children[sortIndex].innerText;
        b = b.children[sortIndex].innerText;
        return a.localeCompare(b, undefined, { numeric: doNumeric, sensitivity: "base", usage: "sort" });
    }

    sizeSort(a, b, sortIndex) {
        a = TableSort.prototype.convertToBytes(a.children[sortIndex].innerText);
        b = TableSort.prototype.convertToBytes(b.children[sortIndex].innerText);
        return a - b;
    }

    folderSort(sortIndex, sortFunction, extraParam) {
        return (a, b) => {
            const aIsDir = a.children[3].innerText === "";
            const bIsDir = b.children[3].innerText === "";
            if (aIsDir === bIsDir) {
                return this.currentOrder[sortIndex] * sortFunction(a, b, sortIndex, extraParam);
            }
            else {
                return bIsDir - aIsDir;
            }
        }
    }

    addEventListener() {
        document.addEventListener('keydown', (event) => {
            if (manager.tableView.tableElements.length === 0 || document.querySelector("input:focus, textarea:focus") !== null) {
                return;
            }
            const currentSelected = document.querySelector(`#${manager.tableView.tableElementId} .selectedtablerow`);
            const key = event.key;
            if (key === "ArrowUp") {
                event.preventDefault();
                this.setActive(currentSelected.previousElementSibling, currentSelected);
            }
            else if (key === "ArrowDown") {
                event.preventDefault();
                this.setActive(currentSelected.nextSibling, currentSelected);
            }
            else if (key === "Enter") {
                event.preventDefault();
                let currentFile;
                for (const file of manager.tableView.serverResponse.folder.entries) {
                    if (file.index === parseInt(currentSelected.id.substring(4))) {
                        currentFile = file;
                        break;
                    }
                }
                if (currentFile === undefined) {
                    manager.tableView.addStringToCurrentFolderPath("..");
                    manager.tableView.displayCurrentFolder();
                }
                else if (currentFile.isDir && currentFile.isExecutable && currentFile.isReadable) {
                    manager.tableView.addStringToCurrentFolderPath(currentFile.fileName);
                    manager.tableView.displayCurrentFolder();
                } else if (!currentFile.isDir && currentFile.isReadable) {
                    manager.editor.showFile(currentFile);
                }
            }
        });
    }

    setFirstEntryActive() {
        const first = document.querySelector(`#${manager.tableView.tableElementId} tbody tr:first-child`);
        first.classList.add("selectedtablerow");
    }

    setActive(newElement, oldElement) {
        if (newElement !== null) {
            oldElement.classList.remove("selectedtablerow");
            newElement.classList.add("selectedtablerow");
        }
    }
}
