let currentlyOpenFile;
let currentlyOpenFileDir;

async function editFile(file, folderPath) {
    currentlyOpenFile = file;
    currentlyOpenFileDir = folderPath;
    const fileContent = await httpPOST("webInterface.php", {action: "getsinglefile", folder: folderPath, ids: file.index});
    document.getElementById("textarea").value = fileContent;
}
