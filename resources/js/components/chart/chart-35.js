
export function initChartThirtyFive() {
    const chartEl = document.querySelector('#chartThirtyFive');
    if (chartEl) {

        const options = {
            chart: {
                type: "donut",
                height: 320,
                toolbar: { show: false },
                fontFamily: "Outfit, sans-serif",
            },
            series: [900, 700, 850],
            labels: ["ChatGPT", "Gemini", "xAI"],
            colors: ["#7592FF", "#7CD4FD", "#BDB4FE"],
            plotOptions: {
                pie: {
                    donut: {
                        size: "70%",
                        background: "transparent",
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: "13px",
                                fontWeight: "400",
                                color: "#667085",
                                offsetY: 20,
                            },
                            value: {
                                show: true,
                                fontSize: "28px",
                                fontWeight: "700",
                                color: "#101828",
                                offsetY: -16,
                                formatter: () => "13.5M",
                            },
                            total: {
                                show: true,
                                label: "Total API Token used",
                                fontSize: "13px",
                                fontWeight: "400",
                                color: "#667085",
                                formatter: () => "13.5M",
                            },
                        },
                    },
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: false,
                width: 0,
            },
            legend: {
                show: true,
                position: "bottom",
                horizontalAlign: "center",
                markers: {
                    shape: "circle",
                    size: 6,
                    offsetX: -2,
                    strokeWidth: 0,
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 4,
                },
                labels: {
                    colors: "#344054",
                },
                fontSize: "13px",
            },
            tooltip: {
                enabled: false,
            },
        };

        const chart = new ApexCharts(chartEl, options);
        chart.render();
    }
}
export default initChartThirtyFive;
