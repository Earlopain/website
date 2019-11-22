class TableView {
    constructor(tableElementId, currentFolderElementId, currentUserElementId) {
        this.tableElementId = tableElementId;
        this.currentFolderElementId = currentFolderElementId;
        this.currentUserElementId = currentUserElementId;
        this.serverResponse;
        this.tableElements = [];
        window.addEventListener("DOMContentLoaded", () => {
            document.getElementById(this.currentFolderElementId).addEventListener("keydown", event => {
                if (event.keyCode === 13) {
                    displayCurrentFolder();
                }
            });
            window.addEventListener("popstate", this.loadFromUrl);
            const slider = document.getElementById("filenameslider");
            const fileNameHeader = document.querySelector(`#${this.tableElementId} th.filename`);
            slider.value = fileNameHeader.getBoundingClientRect().width;
            slider.addEventListener("input", event => {
                const newWidth = event.currentTarget.value + "px";
                fileNameHeader.style.width = newWidth;
                for (const entry of this.tableElements) {
                    entry.children[1].style.width = newWidth;
                }
            });
            this.loadFromUrl();
            registerTableSort();
        });
    }

    loadFromUrl() {
        const currentUrl = new URL(location.href);
        const folder = currentUrl.searchParams.get("folder");
        this.setCurrentFolderPath(folder === null ? "/" : atob(folder));
        this.displayCurrentFolder(false);
    }

    async displayCurrentFolder(pushToHistory = true) {
        const folderPath = this.getCurrentFolderPath();
        this.serverResponse = JSON.parse(await serverRequest("getdir", { folder: folderPath }));
        if (this.serverResponse.folder.entries.length === 0) {
            this.setCurrentFolderPath("/");
            this.displayCurrentFolder();
            return;
        }
        document.getElementById(this.currentUserElementId).innerHTML = this.serverResponse.username;
        if (pushToHistory) {
            const currentUrl = new URL(location.href);
            currentUrl.searchParams.set("folder", btoa(this.serverResponse.folder.currentFolder));
            window.history.pushState({}, null, currentUrl.href);
        }

        this.serverResponse.folder.entries = this.serverResponse.folder.entries.sort((a, b) => {
            if (a.isDir === b.isDir) {
                return a.fileName.localeCompare(b.fileName, undefined, { numeric: true, sensitivity: "base" });
            } else {
                return b.isDir - a.isDir;
            }
        });
        this.removeAllEntires();

        let newTableContent = document.createElement("tbody");

        if (this.serverResponse.folder.currentFolder !== "/") {
            const parentFolder = this.generateFileElement(this.serverResponse.folder.parentFolder);
            newTableContent.appendChild(parentFolder);
        }
        for (const entry of this.serverResponse.folder.entries) {
            const element = this.generateFileElement(entry);
            newTableContent.appendChild(element);
        }
        this.tableElements = newTableContent.querySelectorAll(`tr`);
        document.querySelector("#" + this.tableElementId).appendChild(newTableContent);
        setCurrentRows();
    }

    generateFileElement(file) {
        let row = document.createElement("tr");
        row.id = "file" + file.index;
        let checkbox = document.createElement("input");
        checkbox.classList.add("checkbox");
        checkbox.type = "checkbox";
        row.appendChild(checkbox);
        let fileNameColumn = this.createTableColumn("filename", file.fileName);
        this.addFolderEventListener(fileNameColumn, file);
        this.addFileEditEventListener(fileNameColumn, file);
        row.appendChild(fileNameColumn);
        row.appendChild(this.createTableColumn("ext", file.ext));
        row.appendChild(this.createTableColumn("size", file.isDir ? "" : file.size));
        row.appendChild(this.createTableColumn("user", file.userString));
        row.appendChild(this.createTableColumn("group", file.groupString));
        row.appendChild(this.createTableColumn("perms", file.perms));
        row.appendChild(this.createTableColumn("readable", file.isReadable));
        row.appendChild(this.createTableColumn("writeable", file.isWriteable));
        return row;
    }

    addFolderEventListener(element, file) {
        if (file.isDir && file.isExecutable) {
            element.addEventListener("click", () => {

                this.addStringToCurrentFolderPath(file.fileName);
                this.displayCurrentFolder();
            });
        }
    }
    addFileEditEventListener(element, file) {
        if (!file.isDir && file.isReadable) {
            element.addEventListener("click", () => {
                editor.showFile(file, this.getCurrentFolderPath());
            });
        }
    }

    addStringToCurrentFolderPath(fileName) {
        const current = this.getCurrentFolderPath();
        if (fileName === "..") {
            const splitted = current.split("/");
            splitted.pop();
            this.setCurrentFolderPath(splitted.length === 1 ? "/" : splitted.join("/"));
        } else {
            let addition = current === "/" ? "" : "/";
            this.setCurrentFolderPath(current + addition + fileName);
        }
    }
    createTableColumn(type, content) {
        let col = document.createElement("td");
        col.classList.add(type);
        col.innerHTML = content;
        return col;
    }

    getCurrentFolderPath() {
        const currentValue = document.getElementById(this.currentFolderElementId).value;
        const trailingSlashRemoved = currentValue === "/" ? "/" : currentValue.replace(/[\/]*$/, "")
        return trailingSlashRemoved;
    }

    setCurrentFolderPath(folder) {
        document.getElementById(this.currentFolderElementId).value = folder;
    }

    getHeaders() {
        return document.querySelector(`#${this.tableElementId} thead tr`).children;
    }

    removeAllEntires() {
        let currentTableContent = document.querySelector(`#${this.tableElementId} tbody`);
        if (currentTableContent !== null) {
            currentTableContent.remove()
        }
    }
}

let tableView = new TableView("table", "currentfolder", "loggedinas");
