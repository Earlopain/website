let previousContent;
let timeout;

document.getElementById("input").addEventListener("keydown", e => {
    const target = e.target;
    const insertString = "    ";
    if (e.keyCode === 9) {
        e.preventDefault();
        const currentSelection = target.selectionStart;
        if (target.selectionStart || target.selectionStart == '0') {
            const startPos = target.selectionStart;
            const endPos = target.selectionEnd;
            target.value = target.value.substring(0, startPos)
                + insertString
                + target.value.substring(endPos, target.value.length);
        } else {
            target.value += insertString;
        }
        target.setSelectionRange(currentSelection + 4, currentSelection + 4);
    }
});

setInterval(() => {
    let currentContent = document.getElementById("input").value;
    const output = document.getElementById("output");
    if (currentContent !== previousContent) {
        previousContent = currentContent;
    }
    else {
        return;
    }
    if (currentContent.startsWith("js")) {
        currentContent = currentContent.replace(/console\.log/g, ";resultString += '<br>' + eval");
        currentContent = currentContent.replace(/;resultString \+= '<br>' \+ eval\(\"/g, ";resultString += '<br>' + (\"");
        const backupString = document.getElementById("form").code.value;
        document.getElementById("form").code.value = currentContent.substring(currentContent.indexOf("\n") + 1);
        document.getElementById("form").submit();
        timeout = setTimeout(() => {
            newIframe.src = "about:blank";
            newIframe.src = "iframe.php";
        }, 100)
        document.getElementById("form").code.value = backupString;
    }
    else if (currentContent.startsWith("php")) {
        const xmlHttp = new XMLHttpRequest();
        xmlHttp.addEventListener("load", () => {
            if (xmlHttp.response.length > 50000) {
                output.innerHTML = "Zu viel output (unconditional loop?)"
            }
            else {
                output.innerHTML = xmlHttp.responseText;
            }
        });
        currentContent = currentContent.replace(/echo /g, "echo '<br>'; echo ")
        let data = new FormData();
        data.append("code", currentContent.substring(currentContent.indexOf("\n") + 1));
        xmlHttp.open("POST", "interpreter.php", true);
        xmlHttp.send(data);
    }
    else {

    }

}, 1000)
