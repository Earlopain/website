let previousContent;
let timeout;

document.getElementById("input").onkeydown = (e) => {
    var target = e.target;
    var insertString = "    ";
    if (e.keyCode === 9) {
        e.preventDefault();
        var currentSelection = target.selectionStart;
        if (target.selectionStart || target.selectionStart == '0') {
            var startPos = target.selectionStart;
            var endPos = target.selectionEnd;
            target.value = target.value.substring(0, startPos)
                + insertString
                + target.value.substring(endPos, target.value.length);
        } else {
            target.value += insertString;
        }
        target.setSelectionRange(currentSelection + 4, currentSelection + 4);
    }
}

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
        var xhttp = new XMLHttpRequest();
        xhttp.onload = (e) => {
            if (xhttp.response.length > 10000) {
                output.innerHTML = "Zu viel output (unconditional loop?)"
            }
            else {
                output.innerHTML = xhttp.responseText;
            }
        }
        currentContent = currentContent.replace(/echo /g, "echo '<br>'; echo ")
        let data = new FormData();
        data.append("code", currentContent.substring(currentContent.indexOf("\n") + 1));
        xhttp.open("POST", "interpreter.php", true);
        xhttp.send(data);
    }
    else {

    }

}, 1000)
