async function loadGraph(id) {
    const data = await loadData(id);

    let lines = data.split("\n");
    lines.shift();  //removes cvs definition
    lines.pop();    //removes last line which is empty

    let dataset = [];
    //remove invalid entries and replace with 0
    lines.forEach(line => {
        let count = line.split(",")[1];
        let time = line.split(",")[0];
        count = (count === "undefined" || count === "" || !count) ? 0 : count;
        dataset.push({ "y": count, "time": time });
    });
    //put y values in a seperate array for easy access
    let numbers = [];
    dataset.forEach(element => {
        if (element["y"] !== 0)
            numbers.push(element["y"]);
    });
    //populate statistics labels
    document.getElementById("lasthour").innerHTML = joinedLastXHours(dataset, 1);
    document.getElementById("last6hours").innerHTML = joinedLastXHours(dataset, 6);
    document.getElementById("last12hours").innerHTML = joinedLastXHours(dataset, 12);
    document.getElementById("lastday").innerHTML = joinedLastXHours(dataset, 24);
    document.getElementById("total").innerHTML = joinedSinceTracked(dataset);
    document.getElementById("currentcount").innerHTML = dataset[dataset.length - 1]["y"];
    //max and min display of the y axis + some buffer in both directions
    const max = Math.max(...numbers) + 25;
    const min = Math.min(...numbers) - 25;
    //replace 0 values with the min dispaly value so the values stop at the x axis
    dataset.forEach(element => {
        if (element["y"] === 0)
            element["y"] = min;
    });

    let margin = { top: 25, right: 25, bottom: 50, left: 50 }
        , width = 1000
        , height = 500;
    // The number of datapoints
    let n = lines.length;

    // X scale will use the index of our data
    const minDate = dateStringToDate(dataset[0].time);
    const maxDate = dateStringToDate(dataset[dataset.length - 1].time);
    console.log(minDate);
    console.log(maxDate);
    let xScale = d3.scaleTime()
        .domain([minDate, maxDate]) // input
        .range([0, width]); // output

    let yScale = d3.scaleLinear()
        .domain([min, max]) // input 
        .range([height, 0]); // output 

    // Add the SVG to the page
    let svg = d3.select("body").append("svg")
        .attr("width", width + margin.left + margin.right)
        .attr("height", height + margin.top + margin.bottom)
        .attr("id", "svg")
        .attr("float", "left")
        .append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

    // Call the x axis in a group tag
    svg.append("g")
        .attr("class", "x axis")
        .attr("transform", "translate(0," + height + ")")
        .call(d3.axisBottom(xScale)); // Create an axis component with d3.axisBottom

    // Call the y axis in a group tag
    svg.append("g")
        .attr("class", "y axis")
        .call(d3.axisLeft(yScale)); // Create an axis component with d3.axisLeft

    // d3's line generator
    let line = d3.line()
        .x(function (d, i) {
            return xScale(dateStringToDate(d.time));
        }) // set the x values for the line generator
        .y(function (d) { return yScale(d.y); }) // set the y values for the line generator 
        .curve(d3.curveMonotoneX) // apply smoothing to the line

    // Append the path, bind the data, and call the line generator 
    svg.append("path")
        .datum(dataset) // Binds data to the line 
        .attr("class", "line") // Assign a class for styling 
        .attr("d", line); // Calls the line generator 
}

const node = document.getElementById("dropdown");
function changeGraph() {    //gets called if dropdown menu selected value changes
    let svg = document.getElementById("svg");
    svg.parentElement.removeChild(svg);
    loadGraph(node.value);
}
loadGraph(node.value);  //loads the initial graph without user intervention


//simply returns content of a url on same-origin
function loadData(invite) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest();
        request.open("GET", "/projects/visualization/discordoutput/" + invite + ".csv", true);

        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                resolve(request.responseText);
            } else { reject(); }
        };
        request.onerror = () => { reject() };
        request.send();
    })
}

const updateInterval = 20;

function joinedLastXHours(array, hours) {
    const currentDate = dateStringToDate(array[array.length - 1]["time"]);
    const dateWished = new Date(currentDate.getTime() - 1000 * 60 * 60 * hours);
    const point = getNearestDataPoint(array, dateWished, hours);

    let sub = point["y"];
    if (sub === 0)
        return "Data incomplete";
    if (!sub)
        sub = array[0]
    return array[array.length - 1]["y"] - sub;
}

function joinedSinceTracked(array) {
    return array[array.length - 1]["y"] - array[0]["y"];
}

function getNearestDataPoint(array, dateWished, hours) {
    const target = dateWished.getTime();
    let current;
    let bestDif = Number.MAX_SAFE_INTEGER; //lmao dates won't work anymore if we hit that
    let result;
    //loop the array backwards until the distance to the wanted date increases instead of decreaes, we went past the point, so take it
    //skip the ones definatly not in range, every entry will get updated once every 20 seconds or more, so 3 entries are a minute and 3 * hours are
    //as much as get checked in the timeframe we wish to check. Because it can only take longer or exactly that long we can safely skip them
    for (let i = array.length - 1 - (60 / updateInterval * hours); i >= 0; i--) {
        current = dateStringToDate(array[i]["time"]).getTime();
        if (current - target < bestDif && current - target > 0) {
            bestDif = current - target;
            result = array[i];
        }//dif has increased, must have wooshed by the best value already
        else
            break;
    }
    return result;
}

//remove local timezone from date
function dateStringToDate(string) {
    let result = new Date(string);
    result = new Date(result.getTime() + -result.getTimezoneOffset() * 60000);
    return result;
}