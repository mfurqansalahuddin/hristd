
export function initChartTwentySix() {
    const chartEl = document.querySelector('#chartTwentySix');
    if (chartEl) {
        const chartOptions = {
            series: [
                {
                    name: "Online Sales",
                    data: [
                        [new Date("2026-01-01").getTime(), 13000],
                        [new Date("2026-02-01").getTime(), 13800],
                        [new Date("2026-03-01").getTime(), 13200],
                        [new Date("2026-04-01").getTime(), 14200],
                        [new Date("2026-05-01").getTime(), 13900],
                        [new Date("2026-06-01").getTime(), 15240],
                        [new Date("2026-07-01").getTime(), 15600],
                        [new Date("2026-08-01").getTime(), 17000],
                        [new Date("2026-09-01").getTime(), 16500],
                        [new Date("2026-10-01").getTime(), 17500],
                        [new Date("2026-11-01").getTime(), 17200],
                        [new Date("2026-12-01").getTime(), 18000],
                    ],
                },
                {
                    name: "Offline Sales",
                    data: [
                        [new Date("2026-01-01").getTime(), 8500],
                        [new Date("2026-02-01").getTime(), 9200],
                        [new Date("2026-03-01").getTime(), 8800],
                        [new Date("2026-04-01").getTime(), 9800],
                        [new Date("2026-05-01").getTime(), 9400],
                        [new Date("2026-06-01").getTime(), 10490],
                        [new Date("2026-07-01").getTime(), 10800],
                        [new Date("2026-08-01").getTime(), 11500],
                        [new Date("2026-09-01").getTime(), 10800],
                        [new Date("2026-10-01").getTime(), 11800],
                        [new Date("2026-11-01").getTime(), 11200],
                        [new Date("2026-12-01").getTime(), 11800],
                    ],
                },
            ],
            legend: {
                show: true,
                position: "top",
                horizontalAlign: "left",
                markers: {
                    width: 10,
                    height: 10,
                    radius: 50,
                },
                itemMargin: {
                    horizontal: 10,
                },
            },
            colors: ["#465FFF", "#9CB9FF"],
            chart: {
                fontFamily: "Outfit, sans-serif",
                height: 250,
                type: "area",
                toolbar: {
                    show: false,
                },
                zoom: {
                    enabled: false,
                },
            },
            fill: {
                type: "gradient",
                gradient: {
                    enabled: true,
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0,
                    stops: [0, 100],
                },
            },
            stroke: {
                curve: "smooth",
                width: [2, 2],
            },
            markers: {
                size: 0,
            },
            labels: {
                show: false,
                position: "top",
            },
            grid: {
                xaxis: {
                    lines: {
                        show: false,
                    },
                },
                yaxis: {
                    lines: {
                        show: true,
                    },
                },
            },
            dataLabels: {
                enabled: false,
            },
            tooltip: {
                x: {
                    format: "MMM dd, yyyy",
                },
                y: [
                    {
                        formatter: (val) => val.toLocaleString(),
                    },
                    {
                        formatter: (val) => "$" + val.toLocaleString(),
                    },
                ],
                marker: {
                    show: true,
                },
            },
            crosshairs: {
                show: true,
                position: "back",
                stroke: {
                    color: "#465FFF",
                    width: 1,
                    dashArray: 4,
                },
            },
            xaxis: {
                type: "datetime",
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
                tooltip: {
                    enabled: false,
                },
                labels: {
                    format: "MMM",
                    datetimeUTC: false,
                },
            },
            yaxis: {
                min: 0,
                max: 20000,
                tickAmount: 4,
                labels: {
                    formatter: (val) => {
                        if (val === 0) return "0";
                        return (val / 1000).toFixed(0) + "K";
                    },
                },
                title: {
                    style: {
                        fontSize: "0px",
                    },
                },
            },
        };
        const chartTwentySix = new ApexCharts(chartEl, chartOptions);
        chartTwentySix.render();
        return chartTwentySix;
    }
}
export default initChartTwentySix;
