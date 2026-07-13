export const initChartFortyOne = () => {
    const el = document.querySelector("#chartFortyOne");
    if (!el) return;

    const options = {
        chart: {
            type: "radialBar",
            height: 300,
            toolbar: { show: false },
            fontFamily: "Outfit, sans-serif",
        },
        series: [75, 55, 40],
        colors: ["#262E89", "#FEB273", "#BDB4FE"],
        plotOptions: {
            radialBar: {
                startAngle: -90,
                endAngle: 90,
                offsetY: 60,
                hollow: {
                    size: "25%",
                    background: "transparent",
                },
                track: {
                    background: "#F4F5F5",
                    strokeWidth: "100%",
                    margin: 2,
                },
                dataLabels: {
                    name: {
                        show: false,
                    },
                    value: {
                        show: false,
                    },
                    total: {
                        show: false,
                    },
                },
            },
        },
        stroke: {
            lineCap: "butt",
        },
        legend: {
            show: false,
        },
        tooltip: {
            enabled: false,
        },
    };

    const chart = new ApexCharts(el, options);
    chart.render();
};

export default initChartFortyOne;
