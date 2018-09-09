class Terminal {
    constructor() {
        this.root = new Folder("root");
        this.rootDOM = document.getElementById("terminal-container");
        this.currentFolder = this.root;
        this.location = [0];
        this.counter = 0;
        this.done = false;
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
                switch (this.getCurrentElement().action) {
                    case "onedown":
                        this.cdDown();
                        break;
                    default:
                        console.log("unknown action " + this.getCurrentElement.action);
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
        if(this.done){
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

    addFile(text, action) {
        this.currentFolder.addFile(text, action);
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
        let element = document.getElementById("id_0");
        element.classList.add("selected");
        this.location = [0];
        this.hideAll();
        this.showVisible();
        this.done = true;
        //Add event listener for keydown event
        document.addEventListener('keydown', (event) => {
            const keyName = event.key;
            let element = document.getElementById("id_" + this.location.join("_"));
            element.classList.remove("selected");
            if (keyName === "ArrowUp")
                this.moveUp();
            else if (keyName === "ArrowDown")
                this.moveDown();
            else if (keyName === "Enter") {
                this.select();
                this.hideAll();
                this.showVisible();
            }
            element = document.getElementById("id_" + this.location.join("_"));
            element.classList.add("selected");

        });
    }

    showVisible() {
        const currentID = "id_" + this.location.slice(0, -1).join("_");
        const currentFolder = this.getFolderFromLocation2();

        for (let i = 0; i < currentFolder.fs.length; i++) {
            console.log((currentID + "_" + i).replace("__", "_"));
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
}


class Folder {
    constructor(text) {
        this.text = text;
        this.fs = [];
    }

    addFolder(text) {
        this.fs.push(new Folder(text));

    }
    addFile(text, action) {
        this.fs.push(new File(text, action));
    }
}

class File {
    constructor(text, action) {
        this.text = text;
        this.action = action;
    }
}

let terminal = new Terminal();
terminal.startFolder("folder1");
terminal.addFile("file1");
terminal.addFile("file2");
terminal.addFile("file3");
terminal.startFolder("subfolder1");
terminal.startFolder("subfolder2");
terminal.startFolder("subfolder3");
terminal.addFile("file9");
terminal.addFile("file10");
terminal.endFolder();
terminal.endFolder();
terminal.endFolder();
terminal.endFolder();
terminal.startFolder("folder2");
terminal.addFile("file4");
terminal.addFile("file5");
terminal.endFolder();
terminal.startFolder("folder3");
terminal.addFile("file6");
terminal.addFile("file7");
terminal.addFile("file8");
terminal.endFolder();
terminal.finish();