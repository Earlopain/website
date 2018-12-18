const fs = require("fs");
const request = require("request");

const folder = "/media/earlopain/4TB/Pictures/e621/all"
const trackingTags = [["male", ["solo", "solo_focus"]], ["female", ["solo", "solo_focus"]], ["male/male"], ["male/female"], ["female/female"]];
const naming = ["solo male", "solo female", "gay", "straight", "lesbian"];
if(trackingTags.length !== naming.length)
    throw new Error("You must provice a name for everything");
let cvs = "time," + naming.join(",") + "\n";

let parsedPosts = {};

try {
    parsedPosts = JSON.parse(fs.readFileSync(__dirname + "/posts.json"));
} catch (e) { };

async function main() {
    const files = fs.readdirSync(folder).map(filename => {
        return {
            name: filename,
            time: Math.floor(fs.statSync(folder + "/" + filename).mtimeMs)
        }
    }).sort((a, b) => { return a.time - b.time });
    const tagCounter = [];
    for (const tag of trackingTags) {
        tagCounter.push(0);
    }
    let counter = 0;
    for (const file of files) {
        const md5 = file.name.split(".")[0];
        let json;
        counter++;
        if (parsedPosts[md5])
            json = parsedPosts[md5];
        else {
            json = await getJSON("https://e621.net/post/show.json?md5=" + md5);
            console.log(counter + "/" + files.length);
            parsedPosts[md5] = json;
        }
        const tags = json.tags.split(" ");
        for (let i = 0; i < trackingTags.length; i++) {
            if (checkFilter(trackingTags[i], tags)) {
                tagCounter[i]++;
            }
        }
        cvs += file.time + "," + Object.keys(tagCounter).map(key => tagCounter[key]).join(",") + "\n";

    }
    fs.writeFileSync(__dirname + "/posts.json", JSON.stringify(parsedPosts, null, 4));
    fs.writeFileSync(__dirname + "/data.csv", cvs);
}

function checkFilter(filter, tags) {
    for (const section of filter) {
        //only a single tag
        if (typeof section === "string") {
            if (section.startsWith("-") && tags.includes(section.substr(1)))
                return false;
            else if (!tags.includes(section)) {
                return false;
            }
        }
        //at least one tag must be fullfilled
        else {
            let match = false;
            for (const tag of section) {
                if (tags.includes(tag)) {
                    match = true;
                    break;
                }
            }
            if (!match)
                return false;
        }
    }
    return true;
}


main();

function getJSON(url) {
    return new Promise(function (resolve, reject) {
        request(url, { headers: { 'User-Agent': 'test/earlopain' } }, (error, response, body) => {
            body = JSON.parse(body);
            resolve(body);
        })
    });
}