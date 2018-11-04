const fs = require("fs");

const files = ["b2tDDVd", "eC72Vj9", "Z9Zc9cE"];
const stopHere = [2387, 3886, 3897];    //on which line the last wrong timezone is located
const timeZoneOffset = 1    //in hours



for (let i = 0; i < files.length; i++) {
    const string = fs.readFileSync("./discordouput/" + files[i] + ".csv", "utf8");
    let lines = string.split("\n");
    for (let j = 1; j < stopHere[i]; j++) {
        const split = lines[j].split(",");
        if (split[0] === "Down" || split[1] === "undefined") {
            lines[j] = ""

        }
        else
            lines[j] = getTime(dateStringToDate(split[0])) + "," + split[1] + ",";
    }
    lines = removeA(lines, "");
    fs.writeFileSync("./discordoutput/" + files[i] + ".csv", lines.join("\n"));
}

function dateStringToDate(string) {
    let result = new Date(string);
    result = new Date(result.getTime() + -result.getTimezoneOffset() * 60000 - 60000 * 60 * timeZoneOffset);
    return result;
}

function getTime(date) {
    const hour = date.getUTCHours();
    const minutes = date.getUTCMinutes();
    const seconds = date.getUTCSeconds();
    const result = date.getUTCFullYear() + "-" + (date.getUTCMonth() + 1) + "-" + date.getUTCDate() + " " + (hour.toString().length === 1 ? "0" + hour : hour) + ":" + (minutes.toString().length === 1 ? "0" + minutes : minutes) + ":" + (seconds.toString().length === 1 ? "0" + seconds : seconds);
    return result;
}

function removeA(arr) {
    var what, a = arguments, L = a.length, ax;
    while (L > 1 && arr.length) {
        what = a[--L];
        while ((ax = arr.indexOf(what)) !== -1) {
            arr.splice(ax, 1);
        }
    }
    return arr;
}