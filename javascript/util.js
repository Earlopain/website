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
                resolve(request.responseText);
            } else { reject(); }
        };
        request.onerror = () => { reject() };
        request.send(JSON.stringify(data));
    })
}