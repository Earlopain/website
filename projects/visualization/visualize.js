async function loadGraph(id) {
    const data = await loadData(id);

    let lines = data.split("\n");
    lines.shift();  //removes cvs definition
    lines.pop();    //removes last line which is empty

    let dataset = [];
    //remove invalid entries and replace with 0
    lines.forEach(line => {
        let count = line.split(",")[1];
        count = (count === "undefined" || count === "" || !count) ? 0 : count;
        dataset.push({ "y": count });
    });
    //put y values in a seperate array for easy access
    let numbers = [];
    dataset.forEach(element => {
        if (element["y"] !== 0)
            numbers.push(element["y"]);
    });
    let allNumbers = [];
    dataset.forEach(element => {
        allNumbers.push(element["y"]);
    });
    document.getElementById("lasthour").innerHTML = joinedLastXHours(allNumbers, 1);
    document.getElementById("last6hours").innerHTML = joinedLastXHours(allNumbers, 6);
    document.getElementById("last12hours").innerHTML = joinedLastXHours(allNumbers, 12);
    document.getElementById("lastday").innerHTML = joinedLastXHours(allNumbers, 24);
    document.getElementById("total").innerHTML = joinedSinceTracked(allNumbers);
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
    let xScale = d3.scaleLinear()
        .domain([0, n - 1]) // input
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
        .x(function (d, i) { return xScale(i); }) // set the x values for the line generator
        .y(function (d) { return yScale(d.y); }) // set the y values for the line generator 
        .curve(d3.curveMonotoneX) // apply smoothing to the line

    // Append the path, bind the data, and call the line generator 
    svg.append("path")
        .datum(dataset) // Binds data to the line 
        .attr("class", "line") // Assign a class for styling 
        .attr("d", line); // Calls the line generator 
}

const node = document.getElementById("dropdown");
function changeGraph() {
    let svg = document.getElementById("svg");
    svg.parentElement.removeChild(svg);
    loadGraph(node.value);
}
loadGraph(node.value);



function loadData(invite) {
    return new Promise((resolve, reject) => {
        let request = new XMLHttpRequest();
        request.open("GET", "/projects/datavisualisation/discordoutput/" + invite + ".csv", true);

        request.onload = () => {
            if (request.status >= 200 && request.status < 400) {
                resolve(request.responseText);
            } else { reject(); }
        };
        request.onerror = () => { reject() };
        request.send();

        //$.get("/projects/datavisualisation/discordoutput/" + invite + ".csv", function (data) {
        //   resolve(data);
        //});
    })
}

const updateInterval = 20;

function joinedLastXHours(array, hours) {
    let sub = array[array.length - 1 - 3 * 60 * hours];
    if (!sub && sub !== 0)
        sub = array[0];
    if (sub === 0)
        return "Data incomplete"
    return array[array.length - 1] - sub;
}

function joinedSinceTracked(array) {
    return array[array.length - 1] - array[0];
}