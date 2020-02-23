async function loadGraph(id) {
    const data = await loadData(id);
    if (!data) {
        infoMessage("Please wait a few seconds before checking newly added servers", "info");
        return;
    }

    let allLines = data.split("\n");
    allLines.shift();  //removes cvs definition
    allLines.pop();    //removes last line which is empty
    if (allLines.length === 1) {   //no use in display just 1 point, wait for at least 2 so we can draw a line
        infoMessage("Please wait a few seconds before checking newly added servers", "info");
        return;
    }

    let lines = [];
    const currentDate = new Date();
    const timeWindow = 60 * 60 * 24 * 7;
    //filter out the lines not in the timeWindow
    allLines.forEach((line, index) => {
        let split = line.split(",");
        let time = split[0] * 1000;
        let count = split[1];
        if (time > currentDate.getTime() - timeWindow * 1000)
            lines.push({ "count": count, "time": time });
    });


    const maxDataPoints = 250 - 1;
    let dataset = [];
    //don't let factor fall under 2 because you can't realy pic something in the middle of [1,2] for example
    const factor = lines.length / maxDataPoints < 2 ? 1 : lines.length / maxDataPoints;
    let counter = 0;
    lines.forEach((line, index) => {
        //if we want more points than available just use all
        if (!(maxDataPoints > lines.length)) {
            //% with decimal is shit, never really zero so we have to look when the value wraps around
            if (index % factor > counter) {
                counter = index % factor;
                return;
            }
            counter = 0;
        }

        dataset.push({ "count": line.count, "time": line.time });
    });//Alawys push the last entry so the timeframe is the same no matter how many datapoint we use
    dataset.push({ "count": lines[lines.length - 1].count, "time": lines[lines.length - 1].time });
    console.log(dataset.length);
    //populate statistics labels
    document.getElementById("lasthour").innerHTML = joinedLastXHours(dataset, 1, timeWindow);
    document.getElementById("last6hours").innerHTML = joinedLastXHours(dataset, 6, timeWindow);
    document.getElementById("last12hours").innerHTML = joinedLastXHours(dataset, 12, timeWindow);
    document.getElementById("lastday").innerHTML = joinedLastXHours(dataset, 24, timeWindow);
    document.getElementById("total").innerHTML = joinedSinceTracked(allLines[0], allLines[allLines.length - 1]);
    document.getElementById("currentcount").innerHTML = dataset[dataset.length - 1].count;

    //max and min display of the y axis + some buffer in both directions
    const max = Math.max(...Object.keys(dataset).map((key) => { return dataset[key].count })) + 25;
    const min = Math.min(...Object.keys(dataset).map((key) => { return dataset[key].count })) - 25;

    let margin = { top: 25, right: 75, bottom: 50, left: 50 }, width = 1000, height = 500;

    // X scale will use the dates of our data
    const minDate = dataset[0].time;
    const maxDate = dataset[dataset.length - 1].time;
    let xScale = d3.scaleTime()
        .domain([minDate, maxDate]) // input
        .range([0, width]); // output

    let yScale = d3.scaleLinear()
        .domain([min, max]) // input 
        .range([height, 0]); // output 
    //firstly remove the old graph
    let svgOld = document.getElementById("svg");
    svgOld.parentElement.removeChild(svgOld);
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
        .call(d3.axisLeft(yScale)) // Create an axis component with d3.axisLeft

    // d3's line generator
    let line = d3.line()
        .x(function (d, i) {
            return xScale(d.time);
        }) // set the x values for the line generator
        .y(function (d) { return yScale(d.count); }) // set the y values for the line generator 
        .curve(d3.curveMonotoneX) // apply smoothing to the line

    // Append the path, bind the data, and call the line generator 
    svg.append("path")
        .datum(dataset) // Binds data to the line 
        .attr("class", "line") // Assign a class for styling 
        .attr("d", line); // Calls the line generator 

    //Show value on mouseover
    let focus = svg.append("g")
        .attr("class", "focus")
        .style("display", "none");

    focus.append("circle")
        .attr("r", 4.5);

    focus.append("text")
        .attr("x", 10)
        .attr("y", -10)

    svg.append("rect")
        .attr("width", width)
        .attr("height", height)
        .style("fill", "none")
        .style("pointer-events", "all")
        .on("mouseover", function () { focus.style("display", null); })
        .on("mouseout", function () { focus.style("display", "hidden"); })
        .on("mousemove", mousemove)
        .on("mouseup", mouseUp)
        .on("mousedown", mouseDown);

    function mousemove() {
        let point = pointAtMouse(this);
        focus.attr("transform", "translate(" + xScale(point.time) + "," + yScale(point.count) + ")");
        focus.select("text").text(point.count);
    }
    function mouseDown() {
    }
    function mouseUp() {
    }

    function pointAtMouse(state) {
        return dataset[indexAtMouse(state)];
    }

    function indexAtMouse(state) {
        return d3.bisector(function (d) { return d.time; }).left(dataset, xScale.invert(d3.mouse(state)[0]), 0);
    }
}

let dropdownAll = document.getElementById("dropdownall");
let dropdownMissing = document.getElementById("dropdownmissing");
populateDropdown()

async function populateDropdown() {
    const json = JSON.parse(await getURL("tracking.json")).servers;
    json.reverse().forEach(element => {
        const option = document.createElement('option');
        option.text = element.name
        option.value = element.id;
        dropdownAll.add(option, 0);
        if (!element.invite)
            dropdownMissing.add(option, 0);
    });
    dropdownAll.selectedIndex = 0;
    loadGraph(dropdownAll.value);  //loads the initial graph without user intervention
}

function changeGraph() {    //gets called if dropdown menu selected value changes
    loadGraph(dropdownAll.value);
}



async function loadData(id) {
    try {
        return await getURL("discordoutput/" + id + ".csv");
    } catch (error) {   //a server was added but the script didn't create the datafile yet
        return undefined;
    }
}

function joinedLastXHours(array, hours, timeframe) {
    return joinedLastXSeconds(array, hours * 60 * 60, timeframe);
}

function joinedLastXSeconds(array, seconds, timeframe) {
    if (seconds > timeframe)
        return ""
    const currentDate = array[array.length - 1].time;
    const dateWished = currentDate - seconds * 1000;
    const point = getNearestDataPoint(array, dateWished);

    let sub = point.count;
    if (!sub)
        sub = array[0];
    return array[array.length - 1].count - sub;
}

function joinedSinceTracked(first, last) {
    return last.split(",")[1] - first.split(",")[1];
}

function getNearestDataPoint(array, dateWished) {
    let mid;
    let lo = 0;
    let hi = array.length - 1;
    while (hi - lo > 1) {
        mid = Math.floor((lo + hi) / 2);
        if (array[mid].time < dateWished) {
            lo = mid;
        } else {
            hi = mid;
        }
    }
    if (dateWished - array[lo].time <= array[hi].time - dateWished) {
        return array[lo];
    }
    return array[hi];
}

async function submitNew() {
    const invite = document.getElementById("textfield").value;
    logResponse(await postURL("discord.php", { "invite": invite }));
}
