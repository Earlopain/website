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
            if (!servers[i].invite)     //no invite for the server, skip
                continue;
            const dateString =  Math.floor(new Date().getTime() / 1000);
            let skip = false;
            let serverSize;
            try {
                serverSize = await getServerSize(servers[i].invite);
            } catch (error) {
                console.log("Network Error");
                skip = true;
            }
            if (!skip) {
                if (serverSize === undefined) { //invite expired
                    delete servers[i].invite;   //frees up the invite so php can set a new one if provided
                    await fs.writeFileSync(filename, JSON.stringify({ servers }));
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
    let json;
    try {
        json = await getJSON("https://discordapp.com/api/v6/invite/" + id + "?with_counts=true");
    } catch (error) {
        throw new Error();
    }
    if (json.code === 10006) //invite invalid
        return undefined;
    return json.approximate_member_count;
}

function getJSON(url) {
    return new Promise((resolve, reject) => {
        request.get(url, (error, response, body) => {
            try {
                resolve(JSON.parse(body));
            } catch (error) {
                reject();
            }
        });
    });
}

function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}