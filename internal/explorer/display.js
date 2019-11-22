class TableView {
    constructor(currentFolderElementId, currentUserElementId) {
        this.currentFolderElementId = currentFolderElementId;
        this.currentUserElementId = currentUserElementId;
        window.addEventListener("DOMContentLoaded", () => {
            document.getElementById(this.currentFolderElementId).addEventListener("keydown", event => {
                if (event.keyCode === 13) {
                    displayCurrentFolder();
                }
            });
            window.addEventListener("popstate", this.loadFromUrl);
            const slider = document.getElementById("filenameslider");
            slider.value = document.getElementById("tableheader").children[1].getBoundingClientRect().width;
            slider.addEventListener("input", event => {
                const newWidth = event.currentTarget.value + "px";
                const header = document.getElementById("tableheader").children[1];
                header.style.width = newWidth;
                const allEntries = document.getElementById("filecontents").children;
                for (const entry of allEntries) {
                    entry.children[1].style.width = newWidth;
                }
            });
            this.loadFromUrl();
            registerTableSort();
        });
        this.fileData = [];
    }

    loadFromUrl() {
        const currentUrl = new URL(location.href);
        const folder = currentUrl.searchParams.get("folder");
        this.setCurrentFolderPath(folder === null ? "/" : atob(folder));
        this.displayCurrentFolder(false);
    }

    async displayCurrentFolder(pushToHistory = true) {
        const folderPath = this.getCurrentFolderPath();
        this.fileData = JSON.parse(await serverRequest("getdir", { folder: folderPath }));
        if (this.fileData.folder.entries.length === 0) {
            this.setCurrentFolderPath("/");
            this.displayCurrentFolder();
            return;
        }
        document.getElementById(this.currentUserElementId).innerHTML = this.fileData.username;
        if (pushToHistory) {
            const currentUrl = new URL(location.href);
            currentUrl.searchParams.set("folder", btoa(this.fileData.folder.currentFolder));
            window.history.pushState({}, null, currentUrl.href);
        }
    
        this.fileData.folder.entries = this.fileData.folder.entries.sort((a, b) => {
            if (a.isDir === b.isDir) {
                return a.fileName.localeCompare(b.fileName, undefined, { numeric: true, sensitivity: "base" });
            } else {
                return b.isDir - a.isDir;
            }
        });
        let container = document.getElementById("filecontents");
        container.innerHTML = "";
        if (this.fileData.folder.currentFolder !== "/") {
            const parentFolder = this.generateFileElement(this.fileData.folder.parentFolder);
            container.appendChild(parentFolder);
        }
        for (const entry of this.fileData.folder.entries) {
            const element = this.generateFileElement(entry);
            container.appendChild(element);
        }
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
}

let tableView = new TableView("currentfolder", "loggedinas");
