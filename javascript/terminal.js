class Terminal {
    constructor(containerID = "terminal-container", controlsID = "terminal-controls") {
        this.root = new Folder("root");
        this.rootDOM = document.getElementById(containerID);
        this.controlsDOM = document.getElementById(controlsID);
        this.currentFolder = this.root;
        this.location = [0];
        this.counter = 0;
        this.done = false;
        this.addCss();
    }

    //keyboard code

    moveUp() {
        if (this.location[this.location.length - 1] === 0)
            return false;
        this.location[this.location.length - 1]--;
        return true;
    }

    moveDown() {
        if (this.location[this.location.length - 1] === this.currentFolder.fs.length - 1)
            return false;
        this.location[this.location.length - 1]++;
        return true;
    }

    select() {
        if (this.selectedIsFile()) {
            if (this.done) {
                const currentElement = this.getCurrentElement();
                switch (currentElement.actionType) {
                    case "onedown":
                        this.cdDown();
                        break;
                    case "href":
                        window.location.href = currentElement.action;
                        break;
                    default:
                        console.log("unknown actionType " + this.getCurrentElement.actionType);
                }
            }
        }
        else {
            this.location.push(0);
            this.currentFolder = this.getFolderFromLocation2();
        }
    }

    cdDown() {
        if (this.location.length === 1)
            return false;
        this.location.pop();
        if (this.done) {
            this.location.pop();
            this.location.push(0);
        }
        this.currentFolder = this.getFolderFromLocation2();
        return true;
    }


    //buildup code

    startFolder(text) {
        this.currentFolder.addFolder(text);
        this.location.push(0);
        this.currentFolder = this.getFolderFromLocation();
        this.currentFolder.addFile("..", "onedown");
        this.counter++;
    }

    addFile(text, actionType, action) {
        this.currentFolder.addFile(text, actionType, action);
        this.location[this.location.length - 1]++;
    }

    endFolder() {
        this.location.pop();
        this.currentFolder = this.getFolderFromLocation();
        this.counter--;
    }

    getFolderFromLocation() {
        let copy = this.location;
        let result = this.root;
        while (copy.length > 1) {
            result = result.fs[result.fs.length - 1];
            copy = copy.slice(1);
        }
        return result;
    }

    getFolderFromLocation2() {
        let copy = this.location;
        let result = this.root;
        while (copy.length > 1) {
            result = result.fs[copy[0]];
            copy = copy.slice(1);
        }
        return result;
    }

    finish() {
        if (this.counter !== 0)
            throw new Error("Invalid call count for start/endFolder");
        this.addToDOM();
        this.addControls();
        let element = document.getElementById("id_0");
        element.classList.add("selected");
        this.location = [0];
        this.handleDOM();
        this.done = true;
        //Add event listener for keydown event
        document.addEventListener('keydown', (event) => {
            const key = event.key;
            let element = document.getElementById("id_" + this.location.join("_"));
            element.classList.remove("selected");
            if (key === "ArrowUp")
                this.moveUp();
            else if (key === "ArrowDown")
                this.moveDown();
            else if (key === "Enter") {
                this.select();
                this.handleDOM();
            }
            element = document.getElementById("id_" + this.location.join("_"));
            element.classList.add("selected");
        });
    }

    addControls() {
        const names = ["ArrowUp", "ArrowDown", "Enter"];
        for (let i = 0; i < names.length; i++) {
            let div = document.createElement("div");
            div.classList.add("control");
            div.appendChild(document.createTextNode(names[i]));
            div.id = names[i];
            div.addEventListener("click", () => {
                let evt = new KeyboardEvent('keydown', { key: div.id });
                document.dispatchEvent(evt);
            });
            this.controlsDOM.appendChild(div);
        }
    }

    handleDOM() {
        this.hideAll();
        this.showVisible();
    }

    showVisible() {
        const currentID = "id_" + this.location.slice(0, -1).join("_");
        const currentFolder = this.getFolderFromLocation2();

        for (let i = 0; i < currentFolder.fs.length; i++) {
            document.getElementById((currentID + "_" + i).replace("__", "_")).classList.remove("invisible");
        }
    }

    hideAll() {
        const hide = document.getElementsByClassName("selectable")
        for (let i = 0; i < hide.length; i++) {
            hide[i].classList.add("invisible");
        }
    }

    addToDOM() {
        while (true) {
            this.counter++;
            if (this.counter > 50)
                break;
            let currentElement = this.getCurrentElement();
            let locationPrev = Array.from(this.location);
            this.select();
            //location changed, it must be a folder
            if (currentElement instanceof Folder) {
                this.rootDOM.appendChild(this.createElement("folder", currentElement.text, locationPrev));
            }
            else {
                this.rootDOM.appendChild(this.createElement("file", currentElement.text, locationPrev));

                //location still the same after going one down, end of folder reached
                if (!this.moveDown()) {
                    while (true) {
                        if (!this.cdDown())
                            return;
                        if (this.moveDown())
                            break;

                    }
                }
            }
        }
    }

    createElement(type, text, location) {
        let div = document.createElement("div");
        div.classList.add("selectable");
        div.classList.add(type);
        div.appendChild(document.createTextNode(text));
        div.id = "id_" + location.join("_");
        return div;
    }

    getCurrentElement() {
        return this.currentFolder.fs[this.location[this.location.length - 1]];
    }

    getCurrentDOM() {
        return document.getElementById("id_" + this.location.join("_"));
    }

    selectedIsFile() {
        return this.currentFolder.fs[this.location[this.location.length - 1]] instanceof File;
    }

    addCss() {
        let head = document.head;
        let link = document.createElement("link");
        link.type = "text/css";
        link.rel = "stylesheet";
        link.href = "/css/terminal.css";

        head.appendChild(link);
    }
}


class Folder {
    constructor(text) {
        this.text = text;
        this.fs = [];
    }

    addFolder(text) {
        this.fs.push(new Folder(text));

    }
    addFile(text, actionType, action) {
        this.fs.push(new File(text, actionType, action));
    }
}

class File {
    constructor(text, actionType, action) {
        this.text = text;
        this.actionType = actionType;
        this.action = action;
    }
}