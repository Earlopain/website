async function fetchCsv(username) {
    const tagGroups = {
        "gay": ["male/male -bisexual -male/female", "male solo -bisexual"],
        "straight": ["male/female -bisexual", "female solo"]
    }
    const files = document.getElementById("folderinput").files;
    let fileDates = {};
    for (const file of files) {
        fileDates[file.name.split(".")[0]] = file.lastModified;
    }

    const url = "getUserFavs.php";

    const response = await postURL(url, {
        username: username,
        tagGroups: tagGroups,
        fileDates: fileDates,
        refreshUserFavs: false
    });
    const json = JSON.parse(response.response);
    let lines = [];
    let stack = [];
    const hovertemplate = "%{y:.2f}%";
    for (const groupName of Object.keys(json.graphData)) {
        stack.push({ x: json.xAxis, y: json.graphData[groupName], hovertemplate: hovertemplate, groupnorm: "percent", stackgroup: "one", name: groupName });
        lines.push({ x: json.xAxis, y: json.graphData[groupName], name: groupName, mode: "lines", visible: false });
    }

    const layout = {
        title: "Degenerate Stats",
        legend: {
            traceorder: "normal"
        },
        xaxis: {
            rangeslider: {}
        },
        yaxis: {
            fixedrange: false,
            side: "left",
            title: "favcount",
            zeroline: false
        },
        font: {
            color: getCssVar("--light-grey")
        },
        paper_bgcolor: "#ffffff00",
        plot_bgcolor: "#ffffffdd",
        updatemenus: [{
            y: 1.3,
            yanchor: "top",
            buttons: [{
                method: "restyle",
                args: ['visible', stack.map(a => true).concat(lines.map(a => false))],
                label: 'Stack'
            }, {
                method: "restyle",
                args: ['visible', stack.map(a => false).concat(lines.map(a => true))],
                label: "Lines"
            }]
        }],
        images: [
            {
                source: "/image.png",
                x: 0.25,
                y: 1,
                sizex: 1,
                sizey: 1,
                opacity: 0.03,
                layer: "below"
            }
        ]
    }
    const options = {
        scrollZoom: true,
        responsive: true
    }
    Plotly.newPlot('graph', stack.concat(lines), layout, options);
}
