const request = require("request");
const fs = require("fs");
const filename = __dirname + "/tracking.json"
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


const outputFolder = __dirname + "/discordoutput";
try {
    fs.mkdirSync(outputFolder);
} catch (error) {/*EEXIST*/ }

fs.watchFile(filename, current => {
    update = true;
    servers = JSON.parse(fs.readFileSync(filename)).servers;
});

const checkInterval = 20 //seconds
let update = true;  //true at start(really?) and if server file changed so we know when to update server list
async function main() {
    let skip = false;
    while (true) {
        if (!skip && update) {
            for (let i = 0; i < servers.length; i++) {
                if (!fs.existsSync(outputFolder + "/" + servers[i].id + ".csv")) {
                    fs.appendFileSync(outputFolder + "/" + servers[i].id + ".csv", "Date,Count\n");
                }
            }
            update = false;
        }
        const timeStart = new Date().getMilliseconds();

        for (let i = 0; i < servers.length; i++) {
            if (!skip) {
                if (!servers[i].invite)     //no invite for the server, skip
                    continue;
                const dateString = Math.floor(new Date().getTime() / 1000);

                let serverSize;
                try {
                    serverSize = await getServerSize(servers[i].invite);
                } catch (error) {
                    console.log("Network Error");
                    skip = true;
                }
                if (!skip) {
                    if (serverSize === "Invalid Invite") { //invite expired
                        delete servers[i].invite;   //frees up the invite so php can set a new one if provided
                        await fs.writeFileSync(filename, JSON.stringify({ servers }));
                    }
                    else if (serverSize !== undefined)
                        fs.appendFileSync(outputFolder + "/" + servers[i].id + ".csv", dateString + "," + serverSize + "\n");
                }
            }
        }
        skip = false;
        const runtime = new Date().getMilliseconds() - timeStart;
        await sleep(checkInterval * 1000 - runtime);
    }
}

main();

async function getServerSize(id) {
    let json = await getJSON("https://discordapp.com/api/v6/invite/" + id + "?with_counts=true");
    if (json.code === 10006 && json.message === "Unknown Invite") //invite invalid
        return "Invalid Invite";
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