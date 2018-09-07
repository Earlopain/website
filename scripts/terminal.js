class Terminal {
    constructor() {
        this.root = new Folder("root", undefined);
        this.currentFolder = this.root;
        this.location = [];
        this.counter = 0;
    }

    startFolder(text) {
        this.currentFolder.addFolder(text);
        this.location.push(0);
        this.currentFolder = this.getFolderFromLocation();
        this.counter++;

    }

    addFile(name, action) {
        this.currentFolder.addFile(name, action);
        this.location[this.location.length - 1]++;
    }

    endFolder() {
        this.location.pop();
        this.currentFolder = this.getFolderFromLocation();
        this.counter--;
    }

    finish() {
        if(this.counter !== 0)
            throw new Error("Invalid call count for start/endFolder");
    }

    getFolderFromLocation() {
        let copy = this.location;
        let result = this.root;
        while (copy.length > 0) {
            result = result.fs[result.fs.length - 1];
            copy = copy.slice(1);
        }
        return result;
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
    addFile(name, action) {
        this.fs.push(new File(name, action));
    }
}

class File {
    constructor(name, action) {
        this.name = name;
        this.action = action;
    }
}

let terminal = new Terminal();
terminal.startFolder("folder1");
terminal.addFile("file1");
terminal.addFile("file2");
terminal.addFile("file3");
terminal.startFolder("subfolder1");
terminal.addFile("file9");
terminal.addFile("file10");
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