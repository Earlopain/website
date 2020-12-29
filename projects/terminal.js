class Terminal {
    constructor(containerID = "terminal-container", controlsID = "terminal-controls") {
        this.root = new Folder("root", null);
        this.rootDom = document.getElementById(containerID);
        this.controlsDom = document.getElementById(controlsID);
        this.currentFolder = this.root;
        this.location = 0;
        this.idCounter = 0;
        this.addCss();
    }

    parseFromJson(json) {
        for (const key of Object.keys(json)) {
            const value = json[key];
            if (typeof value === "string") {
                if (value.startsWith("http") || value.startsWith("mailto") || value.startsWith("/")) {
                    this.addFile(key, "href", value);
                } else {
                    this.addFile(key);
                }
            } else {
                this.startFolder(key);
                this.parseFromJson(value);
                this.endFolder();
            }
        }
    }

    moveUp() {
        if (this.location !== 0) {
            this.location--;
        }
    }

    moveDown() {
        if (this.location !== this.currentFolder.fs.length - 1) {
            this.location++;
        }
    }

    select() {
        if (this.selectedIsFile()) {
            const currentElement = this.getCurrentElement();
            switch (currentElement.actionType) {
                case "onedown":
                    this.cdDown();
                    break;
                case "href":
                    window.location.href = currentElement.action;
                    break;
                default:
                    console.log("unknown actionType " + currentElement.actionType);
            }
        }
        else {
            this.currentFolder = this.currentFolder.fs[this.location];
            this.location = 0;
        }
    }

    cdDown() {
        if (this.currentFolder.parent !== null) {
            this.currentFolder = this.currentFolder.parent;
        }
    }

    startFolder(text) {
        this.currentFolder = this.currentFolder.addFolder(text, this.idCounter++);
        this.location = 0;
    }

    addFile(text, actionType, action) {
        this.currentFolder.addFile(text, actionType, action, this.idCounter++);
        this.location++;
    }

    endFolder() {
        this.location = 0;
        this.currentFolder = this.currentFolder.parent;
    }

    finish() {
        if (this.currentFolder.parent !== null) {
            alert("Invalid call count for start/endFolder");
        }
        this.addToDom(this.root);
        this.addControls();
        this.handleDom();

        document.addEventListener('keydown', (event) => {
            const key = event.key;
            let element = document.getElementById("terminal_item_" + this.getCurrentElement().id);
            element.classList.remove("selected");
            if (key === "ArrowUp")
                this.moveUp();
            else if (key === "ArrowDown")
                this.moveDown();
            else if (key === "Enter") {
                this.select();
                this.handleDom();
            }
            this.markSelected();
        });
        this.markSelected();
    }

    addControls() {
        const names = ["ArrowUp", "ArrowDown", "Enter"];
        for (const name of names) {
            let div = document.createElement("div");
            div.classList.add("control");
            div.appendChild(document.createTextNode(name));
            div.addEventListener("click", () => {
                let evt = new KeyboardEvent('keydown', { key: name });
                document.dispatchEvent(evt);
            });
            this.controlsDom.appendChild(div);
        }
    }

    handleDom() {
        this.hideAll();
        this.showVisible();
    }

    showVisible() {
        for (const file of this.currentFolder.fs) {
            document.getElementById("terminal_item_" + file.id).classList.remove("invisible");
        }
    }

    markSelected() {
        for (const element of document.getElementsByClassName("selected")) {
            element.classList.remove("selected");
        }
        this.getCurrentDomElement().classList.add("selected");
    }

    hideAll() {
        for (const element of document.getElementsByClassName("selectable")) {
            element.classList.add("invisible");
        }
    }

    addToDom(folderToAdd) {
        for (const file of folderToAdd.fs) {
            if (file instanceof Folder) {
                this.rootDom.appendChild(this.createElement("folder", file.text, file.id));
                this.addToDom(file);
            }
            else {
                this.rootDom.appendChild(this.createElement("file", file.text, file.id));
            }
        }
    }

    createElement(type, text, id) {
        let div = document.createElement("div");
        div.classList.add("selectable");
        div.classList.add(type);
        div.appendChild(document.createTextNode(text));
        div.id = "terminal_item_" + id;
        return div;
    }

    getCurrentElement() {
        return this.currentFolder.fs[this.location];
    }

    getCurrentDomElement() {
        return document.getElementById("terminal_item_" + this.getCurrentElement().id);
    }

    selectedIsFile() {
        return this.currentFolder.fs[this.location] instanceof File;
    }

    addCss() {
        let head = document.head;
        let link = document.createElement("link");
        link.type = "text/css";
        link.rel = "stylesheet";
        link.href = "/terminal.css";

        head.appendChild(link);
    }
}

class Folder {
    constructor(text, parent, id) {
        this.text = text;
        this.parent = parent;
        this.fs = [];
        this.id = id;
    }

    addFolder(text, id) {
        const folder = new Folder(text, this, id);
        folder.addFile("..", "onedown");
        this.fs.push(folder);
        return folder;

    }
    addFile(text, actionType, action, id) {
        this.fs.push(new File(text, actionType, action, id));
    }
}

class File {
    constructor(text, actionType, action, id) {
        this.text = text;
        this.actionType = actionType;
        this.action = action;
        this.id = id;
    }
}
