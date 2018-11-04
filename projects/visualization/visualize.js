
async function loadGraph(id) {
    const data = await loadData(id);

    let lines = data.split("\n");
    lines.shift();  //removes cvs definition
    lines.pop();    //removes last line which is empty
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
        dataset.push({ "count": count, "time": time });
    });//Alawys push the last entry so the timeframe is the same no matter how many datapoint we use
    dataset.push({ "count": lines[lines.length - 1].split(",")[1], "time": lines[lines.length - 1].split(",")[0] });
    //populate statistics labels
    document.getElementById("lasthour").innerHTML = joinedLastXHours(dataset, 1);
    document.getElementById("last6hours").innerHTML = joinedLastXHours(dataset, 6);
    document.getElementById("last12hours").innerHTML = joinedLastXHours(dataset, 12);
    document.getElementById("lastday").innerHTML = joinedLastXHours(dataset, 24);
    document.getElementById("total").innerHTML = joinedSinceTracked(dataset);
    document.getElementById("currentcount").innerHTML = dataset[dataset.length - 1].count;
    //max and min display of the y axis + some buffer in both directions
    const max = Math.max(...Object.keys(dataset).map((key) => { return dataset[key].count })) + 25;
    const min = Math.min(...Object.keys(dataset).map((key) => { return dataset[key].count })) - 25;

    let margin = { top: 25, right: 75, bottom: 50, left: 50 }, width = 1000, height = 500;

    // X scale will use the dates of our data
    const minDate = dateStringToDate(dataset[0].time);
    const maxDate = dateStringToDate(dataset[dataset.length - 1].time);

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
        .call(d3.axisLeft(yScale)) // Create an axis component with d3.axisLeft

    // d3's line generator
    let line = d3.line()
        .x(function (d, i) {
            return xScale(dateStringToDate(d.time));
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
        .on("mousemove", mousemove);
    let bisectDate = d3.bisector(function (d) { return dateStringToDate(d.time); }).left;
    function mousemove() {
        var x0 = xScale.invert(d3.mouse(this)[0]),
            i = bisectDate(dataset, x0, 0),
            d0 = dataset[i],
            d1 = dataset[i - 1],
            d = x0 - d0.time > d1.time - x0 ? d1 : d0;
        focus.attr("transform", "translate(" + xScale(dateStringToDate(d.time)) + "," + yScale(d.count) + ")");
        focus.select("text").text(d.count);
    }
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
    const currentDate = dateStringToDate(array[array.length - 1].time);
    const dateWished = new Date(currentDate.getTime() - 1000 * 60 * 60 * hours);
    const point = getNearestDataPoint(array, dateWished, hours);

    let sub = point.count;
    if (sub === 0)
        return "Data incomplete";
    if (!sub)
        sub = array[0]
    return array[array.length - 1].count - sub;
}

function joinedSinceTracked(array) {
    return array[array.length - 1].count - array[0].count;
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
        current = dateStringToDate(array[i].time).getTime();
        if (current - target < bestDif && current - target > 0) {
            bestDif = current - target;
            result = array[i];
        }//dif has increased, must have wooshed by the best value already
        else
            break;
    }//the point we had to check was already the left most one, so return it
    if (!result)
        return array[0];
    return result;
}

//remove local timezone from date
function dateStringToDate(string) {
    let result = new Date(string);
    result = new Date(result.getTime() + -result.getTimezoneOffset() * 60000);
    return result;
}