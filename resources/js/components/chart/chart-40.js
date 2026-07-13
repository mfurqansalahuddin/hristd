export const initChartForty = () => {
    const el = document.querySelector("#chartForty");
    if (!el) return;

    const options = {
        chart: {
            type: "radialBar",
            height: 300,
            toolbar: { show: false },
            fontFamily: "Outfit, sans-serif",
        },
        series: [62.25],
        colors: ["#465FFF"],
        plotOptions: {
            radialBar: {
                startAngle: 0,
                endAngle: 360,
                hollow: {
                    size: "72%",
                    background: "transparent",
                },
                track: {
                    background: "#F2F4F7",
                    strokeWidth: "100%",
                    startAngle: 0,
                    endAngle: 360,
                },
                dataLabels: {
                    name: {
                        show: true,
                        fontSize: "15px",
                        fontWeight: "600",
                        color: "#344054",
                        offsetY: -4,
                        formatter: () => "Total",
                    },
                    value: {
                        show: true,
                        fontSize: "20px",
                        fontWeight: "700",
                        color: "#101828",
                        offsetY: 16,
                        formatter: (val) => `${val}%`,
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

export default initChartForty;
