const request = require("request");
const fs = require("fs");
const filename = "./tracking.json"
let servers;
try {
    servers = JSON.parse(fs.readFileSync(filename)).servers;
} catch (error) {
    if (error.code === "ENOENT") { //file not existant, build from scratch
        servers = { servers: [] };
    }
    else if (error.name === "SyntaxError") {
        console.log(filename + " not in a valid json format, please fix");
        console.log("Terminating...");
        return
    }
    else    //not sure what the error is, better not do anything
        throw error;
}


const outputFolder = "./discordoutput";
try {
    fs.mkdirSync(outputFolder);
} catch (error) {/*EEXIST*/ }

fs.watchFile("./tracking.json", current => {
    update = true;
    servers = JSON.parse(fs.readFileSync(filename)).servers;
});

const checkInterval = 20 //seconds
let update = true;  //true at start(really?) and if server file changed so we know when to update server list
async function main() {
    while (true) {
        if (update) {
            for (let i = 0; i < servers.length; i++) {
                if (!fs.existsSync(outputFolder + "/" + servers[i].id + ".csv")) {
                    fs.appendFileSync(outputFolder + "/" + servers[i].id + ".csv", "Date,Count\n");
                }
            }
            update = false;
        }

        const timeStart = new Date().getMilliseconds();
        for (let i = 0; i < servers.length; i++) {
            const dateString = getTime();
            let skip = false;
            let serverSize;
            try {
                serverSize = await getServerSize(servers[i].invite);
            } catch (error) {
                console.log("Network Error");
                skip = true;
            }
            if (!skip) {
                if (!serverSize) {
                    console.log("Invite expired");
                }
                else
                    fs.appendFileSync(outputFolder + "/" + servers[i].id + ".csv", dateString + "," + serverSize + "\n");
            }
        }
        const runtime = new Date().getMilliseconds() - timeStart;
        await sleep(checkInterval * 1000 - runtime);
    }
}

main();

async function getServerSize(id) {
    const json = await getJSON("https://discordapp.com/api/v6/invite/" + id + "?with_counts=true");
    return json.approximate_member_count;
}

function getJSON(url) {
    return new Promise(resolve => {
        request.get(url, (error, response, body) => {
            resolve(JSON.parse(body));
        });
    });
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

function getTime() {
    const date = new Date();
    const hour = date.getUTCHours();
    const minutes = date.getUTCMinutes();
    const seconds = date.getUTCSeconds();
    const result = date.getUTCFullYear() + "-" + (date.getUTCMonth() + 1) + "-" + date.getUTCDate() + " " + (hour.toString().length === 1 ? "0" + hour : hour) + ":" + (minutes.toString().length === 1 ? "0" + minutes : minutes) + ":" + (seconds.toString().length === 1 ? "0" + seconds : seconds);
    return result;
}