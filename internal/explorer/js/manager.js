let manager = {};
window.addEventListener("DOMContentLoaded", () => {
    manager.tableView = new TableView("table", "currentfolder", "loggedinas");
    manager.sorting = new TableSort(["none", "string", "string", "size", "string", "string", "string", "string", "string"]);
    manager.editor = new Editor("editor", "currentlyviewing");
});
