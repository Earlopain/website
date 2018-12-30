//simply returns content of a url on same-origin
function getURL(url) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest();
        request.open("GET", url, true);
        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                resolve(request.responseText);
            } else { reject(); }
        };
        request.onerror = () => { reject() };
        request.send();
    })
}

//simply returns content of a url on same-origin
function postURL(url, data) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest();
        request.open("POST", url, true);
        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                resolve(request);
            } else { reject(); }
        };
        request.onerror = () => { reject() };
        request.send(JSON.stringify(data));
    })
}

let allBoxes = [];
function infoMessage(message, level) {
    const delay = 3000;
    const textSize = 20;
    const padding = 3;
    let wrapper = document.createElement("div");
    wrapper.style.position = "absolute";
    wrapper.style.top = padding * 2 + "px";
    wrapper.style.left = "50%";
    let infoBox = document.createElement("div");
    infoBox.appendChild(document.createTextNode(message));
    infoBox.style.padding = padding + "px";
    infoBox.style.position = "relative";
    infoBox.style.textAlign = "center";
    infoBox.style.display = "inline-block";
    infoBox.style.borderRadius = "4px";
    //offset needed to not obstruct the other elements
    infoBox.style.top = (textSize + padding * 3) * allBoxes.length + "px";
    switch (level) {
        case "error": case 400:
            infoBox.style.backgroundColor = "rgb(145, 35, 32)";
            break;
        case "info":
            infoBox.style.backgroundColor = "rgb(174, 108, 15)";
            break;
        default:
            infoBox.style.backgroundColor = "rgb(48, 110, 48)";
    }
    infoBox.style.fontSize = (textSize - 3) + "px";
    allBoxes.push(infoBox);
    wrapper.appendChild(infoBox);
    document.body.appendChild(wrapper);
    fade(wrapper);
    //have to do it here, because the rect is 0 only if node is not put on dom
    wrapper.childNodes[0].style.left = wrapper.childNodes[0].getBoundingClientRect().width / -2 + "px";

    function fade(element) {
        setTimeout(() => {
            let op = 1;  // initial opacity
            let timer = setInterval(function () {
                if (op <= 0.05) {
                    //remove the top most element
                    allBoxes.shift();
                    allBoxes.forEach((box, index) => {
                        //update position, because we remove the top one
                        box.style.top = (textSize + padding * 3) * index + "px";
                    });
                    clearInterval(timer);   //remove element, if not visible anymore
                    element.parentNode.removeChild(element);
                }
                element.style.opacity = op;
                element.style.filter = 'alpha(opacity=' + op * 100 + ")";
                op -= op * 0.1;
            }, 50);
        }, delay)
    }
}