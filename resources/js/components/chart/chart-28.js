export function initChartTwentyEight() {
    const chartEl = document.querySelector('#chartTwentyEight');
    if (chartEl) {

        const options = {
            colors: ["#465FFF"],
            series: [
                {
                    name: "Balance",
                    data: [
                        20, 24, 23, 25, 30, 35, 40, 30, 32, 33, 29, 28, 27, 29, 45, 58, 70,
                        80, 72, 68, 85, 79, 77, 75,
                    ],
                },
            ],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "area",
                height: 70,
                sparkline: {
                    enabled: true,
                },
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: true,
                },
            },
            stroke: {
                curve: "smooth",
                width: 2,
            },
            fill: {
                type: "gradient",
                gradient: {
                    enabled: true,
                    opacityFrom: 0.65,
                    opacityTo: 0,
                },
            },
            markers: {
                size: 0,
            },
            tooltip: {
                enabled: false,
            },
        };
        const chartTwentyEight = new ApexCharts(chartEl, options);
        chartTwentyEight.render();
        return chartTwentyEight;
    }
}
export default initChartTwentyEight;

