let zoomArea = [0, 1];

async function loadGraph(id) {
    let oldZoomArea = zoomArea;
    const data = await loadData(id);
    if (!data) {
        setInfoBox("Please wait a few seconds before checking newly added servers");
        return;
    }

    let allLines = data.split("\n");
    allLines.shift();  //removes cvs definition
    allLines.pop();    //removes last line which is empty
    if (allLines.length === 1) {   //no use in display just 1 point, wait for at least 2 so we can draw a line
        setInfoBox("Please wait a few seconds before checking newly added servers");
        return;
    }

    let lines = [];
    //filter out the lines not in the zoomarea
    allLines.forEach((line, index) => {
        if (index / allLines.length > zoomArea[0] && index / allLines.length < zoomArea[1])
            lines.push(line);
        //if there are no more lines in the zoomarea but we got less than 2, add another one regardless
        if (index / allLines.length > zoomArea[1] && lines.length < 2) {
            lines.push(line);
        }
    });


    const maxDataPoints = 250;
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

        let count = line.split(",")[1];
        let time = line.split(",")[0];
        dataset.push({ "count": count, "time": time * 1000 });
    });//Alawys push the last entry so the timeframe is the same no matter how many datapoint we use
    dataset.push({ "count": lines[lines.length - 1].split(",")[1], "time": lines[lines.length - 1].split(",")[0] * 1000 });
    //populate statistics labels
    //but only if displaying the whole thing, eg zoom = [0,1] which is always true if a graph has been selected from the dropdown menu
    if (zoomArea[0] === 0 && zoomArea[1] === 1) {
        document.getElementById("lasthour").innerHTML = joinedLastXHours(dataset, 1);
        document.getElementById("last6hours").innerHTML = joinedLastXHours(dataset, 6);
        document.getElementById("last12hours").innerHTML = joinedLastXHours(dataset, 12);
        document.getElementById("lastday").innerHTML = joinedLastXHours(dataset, 24);
        document.getElementById("total").innerHTML = joinedSinceTracked(dataset);
        document.getElementById("currentcount").innerHTML = dataset[dataset.length - 1].count;
    }

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
        zoomArea = [];
        zoomArea.push(indexAtMouse(this) / maxDataPoints);
    }
    function mouseUp() {
        zoomArea.push(indexAtMouse(this) / maxDataPoints);
        zoomArea.sort();    //sort them so the lowest one is always left
        const diff = oldZoomArea[1] - oldZoomArea[0];   //how much area did the old zoom have?
        zoomArea[0] = oldZoomArea[0] + diff * zoomArea[0];  //go from the old zoom as a start and add the % of the diff * the new zoom
        zoomArea[1] = oldZoomArea[0] + diff * zoomArea[1];
        //don't set the zoom if both points are the same and revert back
        if (zoomArea[0] === zoomArea[1]) {
            zoomArea = oldZoomArea;
            return;
        }
        loadGraph(id);
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
    const json = JSON.parse(await getURL("/serverside/projects/visualization/tracking.json")).servers;
    json.reverse().forEach(element => {
        var option = document.createElement('option');
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
    zoomArea = [0, 1];      //reset zoom
    loadGraph(dropdownAll.value);
}

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

async function loadData(id) {
    try {
        return await getURL("/serverside/projects/visualization/discordoutput/" + id + ".csv");
    } catch (error) {   //a server was added but the script didn't create the datafile yet
        return undefined;
    }
}

function joinedLastXHours(array, hours) {
    const currentDate = array[array.length - 1].time;
    const dateWished = currentDate - 60 * 60 * hours * 1000;
    const point = getNearestDataPoint(array, dateWished);

    let sub = point.count;
    if (!sub)
        sub = array[0];
    return array[array.length - 1].count - sub;
}

function joinedSinceTracked(array) {
    return array[array.length - 1].count - array[0].count;
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
    setInfoBox(await postURL("/serverside/projects/visualization/discord.php", { "invite": invite }));
}

function setInfoBox(text) {
    document.getElementById("errorcontainer").innerHTML = text;
}