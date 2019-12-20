as.dashboard = {};

as.dashboard.initChart = function () {
    var data = {
        labels: months,
        datasets: [
            {
                label: "Iklantext",
                backgroundColor: "transparent",
                borderColor: "#1eacbe",
                pointBackgroundColor: "#1eacbe",
                data: iklantexts
            },
            {
                label: "Iklanimage",
                backgroundColor: "transparent",
                borderColor: "#FF6384",
                pointBackgroundColor: "#FF6384",
                data: iklanimages
            },
        ]
    };

    var ctx = document.getElementById("IklanChart").getContext("2d");
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            scales: {
                xAxes: [{
                    gridLines: {
                        display: true,
                    }
                }],
                yAxes: [{
                    gridLines: {
                        color: "#f6f6f6",
                        zeroLineColor: '#f6f6f6',
                        drawBorder: true
                    },
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {if (value % 1 === 0) {return value;}}
                    }
                }]
            },
            responsive: true,
            legend: {
                display: true
            },
            maintainAspectRatio: false,
            tooltips: {
                titleMarginBottom: 15,
                callbacks: {
                    label: function(tooltipItem, data) {
                        var value = tooltipItem.yLabel,
                        suffix = "New" + " " + (value == 1 ? data.datasets[tooltipItem.datasetIndex].label : data.datasets[tooltipItem.datasetIndex].label + "s");
                        return " " + value + " " + suffix;
                    }
                }
            }
        }
    })
};

$(document).ready(function () {
    as.dashboard.initChart();
});
