export const initChartThirtyOne = () => {
    const el = document.querySelector("#chartThirtyOne");
    if (!el) return;

    const options = {
        chart: {
            type: "bar",
            height: 300,
            toolbar: { show: false },
            fontFamily: "Outfit, sans-serif",
        },
        plotOptions: {
            bar: {
                horizontal: true,
                barHeight: "40%",
                borderRadius: 4,
                borderRadiusApplication: "end",
                dataLabels: {
                    position: "top",
                },
            },
        },
        dataLabels: {
            enabled: false,
        },
        colors: ["#465FFF", "#E4E7EC"],
        series: [
            {
                name: "Category A",
                data: [620, 500, 480, 615, 620],
            },
            {
                name: "Category B",
                data: [350, 505, 395, 205, 350],
            },
        ],
        xaxis: {
            categories: ["Jan", "Feb", "Mar", "Apr", "May"],
            min: 0,
            max: 700,
            tickAmount: 7,
            labels: {
                style: {
                    fontSize: "12px",
                    colors: "#667085",
                },
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: "12px",
                    colors: "#344054",
                },
            },
        },
        legend: {
            show: true,
            position: "top",
            horizontalAlign: "left",
            markers: {
                shape: "circle",
                size: 6,
            },
            itemMargin: {
                horizontal: 12,
            },
            labels: {
                colors: "#344054",
            },
        },
        grid: {
            borderColor: "#F2F4F7",
            strokeDashArray: 0,
            xaxis: {
                lines: {
                    show: true,
                },
            },
            yaxis: {
                lines: {
                    show: false,
                },
            },
        },
        fill: {
            opacity: 1,
        },
        tooltip: {
            x: {
                show: true,
            },
        },
    };

    const chart = new ApexCharts(el, options);
    chart.render();
};

export default initChartThirtyOne;
