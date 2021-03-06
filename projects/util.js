//simply returns content of a url on same-origin
function getURL(url) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest();
        request.open("GET", url, true);
        request.addEventListener("load", () => {
            if (request.status >= 200 && request.status < 400) {
                resolve(request.responseText);
            } else { reject(); }
        });
        request.addEventListener("error", () => { reject() });
        request.send();
    })
}

//simply returns content of a url on same-origin
function postURL(url, data) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest();
        request.open("POST", url, true);
        request.addEventListener("load", () => {
            if (request.status >= 200 && request.status <= 400) {
                resolve(request);
            } else { reject(); }
        });
        request.addEventListener("error", () => { reject() });
        request.send(JSON.stringify(data));
    })
}

function logResponse(request) {
    if (request.status !== 200) {
        infoMessage(request.responseText, request.status);
    }
    else {
        infoMessage("Success");
    }
}

function getCssVar(string) {
    return getComputedStyle(document.body).getPropertyValue(string);
}

let allBoxes = [];
function infoMessage(message, level) {
    let delay = 3000;
    const textSize = 20;
    const padding = 3;
    const rounding = 4
    let wrapper = document.createElement("div");
    wrapper.style.position = "absolute";
    wrapper.style.top = padding * 2 + "px";
    wrapper.style.left = "50%";
    wrapper.style.transform = "translate(-50%)";
    let infoBox = document.createElement("div");
    infoBox.appendChild(document.createTextNode(message));
    infoBox.style.padding = padding + "px";
    infoBox.style.position = "relative";
    infoBox.style.textAlign = "center";
    infoBox.style.display = "inline-block";
    infoBox.style.borderRadius = rounding + "px";
    //offset needed to not obstruct the other elements
    infoBox.style.top = (textSize + padding * 3) * allBoxes.length + "px";
    switch (level) {
        case "error": case 400:
            infoBox.style.backgroundColor = "rgb(145, 35, 32)";
            delay = 10000;
            break;
        case "info":
            infoBox.style.backgroundColor = "rgb(174, 108, 15)";
            break;
        default:
            infoBox.style.backgroundColor = "rgb(48, 110, 48)";
            break;
    }
    infoBox.style.fontSize = (textSize - 3) + "px";
    allBoxes.push(infoBox);
    wrapper.appendChild(infoBox);
    document.body.appendChild(wrapper);
    fade(wrapper);

    function fade(element) {
        setTimeout(() => {
            let op = 1;  // initial opacity
            let timer = setInterval(function () {
                if (op <= 0.15) {
                    //remove the element which turned invisible and save the index
                    let index;
                    for (let i = 0; i < allBoxes.length; i++) {
                        if (allBoxes[i].match === element.match) {
                            index = i;
                            allBoxes.splice(i, 1);
                            break;
                        }
                    }//move all boxes under the removed one up
                    for (let i = index; i < allBoxes.length; i++)
                        updatePosition(allBoxes[i], i);
                    clearInterval(timer);   //remove element, if not visible anymore
                    element.parentNode.removeChild(element);
                }
                element.style.opacity = op;
                element.style.filter = 'alpha(opacity=' + op * 100 + ")";
                op -= op * 0.15;
            }, 50);
        }, delay)
    }
    //gradually move up, stop once the position of the box above has been reached
    function updatePosition(element, index) {
        const wishedPos = (textSize + padding * 3) * index;
        const speed = 4;
        const timer = setInterval(() => {
            if (parseInt(element.style.top) - speed <= wishedPos) {
                element.style.top = wishedPos + "px";
                clearInterval(timer);
            }
            else
                element.style.top = parseInt(element.style.top) - speed + "px";
        }, 25);
    }
}

function getUserTextInput(promt = "Please enter", prefilled = "", callback = userInput => { }) {
    let wrapper = document.createElement("div");
    wrapper.classList.add("user-prompt-text");

    let promtDiv = document.createElement("div");
    promtDiv.innerText = promt;
    promtDiv.classList.add("user-promt-notice");

    const textarea = document.createElement("textarea");
    textarea.value = prefilled;
    let inputArea = document.createElement("div");
    inputArea.appendChild(textarea);
    inputArea.classList.add("user-prompt-textarea-container");
    let buttonDiv = document.createElement("div");
    buttonDiv.classList.add("user-promt-button");
    let buttonSubmit = document.createElement("button");
    buttonSubmit.appendChild(document.createTextNode("Submit"));

    buttonSubmit.addEventListener("click", () => {
        callback(textarea.value);
        wrapper.remove();
    }, { once: true });

    buttonDiv.appendChild(buttonSubmit);
    wrapper.appendChild(promtDiv);
    wrapper.appendChild(inputArea);
    wrapper.appendChild(document.createElement("br"));
    wrapper.appendChild(buttonDiv);
    document.body.appendChild(wrapper);
}
