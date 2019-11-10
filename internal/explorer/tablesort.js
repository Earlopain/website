let currentOrder = [];
let sortType = ["none", "string", "string", "string", "string", "string", "size", "string", "string"];

function registerTableSort() {
    let counter = 0;
    for (const header of document.getElementById("tableheader").children) {
        header.addEventListener("click", () => sortColum(header.cellIndex));
        currentOrder.push(1);
        counter++;
    }
}

function sortColum(index) {
    if(sortType[index] === "none")
        return;
    let container = document.getElementById("filecontents");
    let allEntries = container.children;

    allEntries = [...allEntries].slice();
    container.innerHTML = "";
    const dotdot = allEntries.shift();
    switch (sortType[index]) {
        case "string":
            allEntries = allEntries.sort(stringSort.bind(index));
            break;
        case "size":
                allEntries = allEntries.sort(sizeSort.bind(index));

        default:
            break;
    }

    currentOrder[index] *= -1;
    container.appendChild(dotdot);

    for (const entry of allEntries) {
        container.appendChild(entry);
    }
}

function convertToBytes(input) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const split = input.split(" ");
    const index = units.indexOf(split[1]);
    return split[0] *= Math.pow(1024, index);
}

function stringSort(a, b) {
    return currentOrder[this] * ('' + a.children[this].innerText).localeCompare(b.children[this].innerText, undefined, {numeric: true, sensitivity: "base"});
}

function sizeSort(a, b) {
    a = convertToBytes(a.children[this].innerText);
    b = convertToBytes(b.children[this].innerText);
    return currentOrder[this] * (a - b);
}
