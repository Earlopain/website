const request = require("request");
const fs = require("fs");
const userFolder = __dirname + "/userfavs";
const postFolder = __dirname + "/postjson";

let updateJSON = true;

async function main() {
    //should always e present, because php checks for argument
    const username = process.argv[2];
    const favsmd5 = await getFavsMd5(username);
    if (updateJSON) {
        fs.writeFileSync(userFolder + "/" + username + ".json", JSON.stringify(favsmd5));
    }
    const fullPosts = getPosts(favsmd5);
    //php will pick this up with shell_exec and return it to the browser
    console.log(JSON.stringify(fullPosts));
}

main();

function getPosts(md5array) {
    let result = [];
    md5array.forEach(md5 => {//should never fail, because all posts were previously populated
        result.push(JSON.parse(fs.readFileSync(postFolder + "/" + md5 + ".json", "utf8")));
    });
    return result;
}

async function getFavsMd5(username) {
    let result = [];
    let page = 1;
    const firstPageJSON = await getJSON("https://e621.net/post/index.json?tags=fav:" + username + "&limit=320&page=1");
    if (!updateNeeded(username, firstPageJSON))
        return JSON.parse(fs.readFileSync(userFolder + "/" + username + ".json"));

    let currentFavs = [];
    if (fs.existsSync(userFolder + "/" + username + ".json"))
        currentFavs = JSON.parse(fs.readFileSync(userFolder + "/" + username + ".json"));
    result = currentFavs.slice();
    while (true) {
        const json = page === 1 ? firstPageJSON : await getJSON("https://e621.net/post/index.json?tags=fav:" + username + "&limit=320&page=" + page);
        for (let i = 0; i < json.length; i++) {
            //got a hash we already saw, backtracked to already known values
            if (currentFavs.includes(json[i].md5))
                return result;
            if (!fs.existsSync(postFolder + "/" + json[i].md5 + ".json"))
                fs.writeFileSync(postFolder + "/" + json[i].md5 + ".json", JSON.stringify(json[i]));
            result.push(json[i].md5);
        }
        if (json.length !== 320)
            return result;
        page++;
    }
}

function updateNeeded(username, json) {
    //user not encountered yet, update definatly needed
    if (!fs.existsSync(userFolder + "/" + username + ".json"))
        return true;
    const userjson = JSON.parse(fs.readFileSync(userFolder + "/" + username + ".json"));
    //first saved value does not reflect first online value, update
    if (userjson[0] !== json[0].md5)
        return true;
    updateJSON = false;
    return false;
}

function getJSON(url) {
    return new Promise(function (resolve, reject) {
        request(url, { headers: { 'User-Agent': 'test/earlopain' } }, (error, response, body) => {
            body = JSON.parse(body);
            resolve(body);
        })
    });
}