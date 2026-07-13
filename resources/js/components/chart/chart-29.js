export function initChartTwentyNine() {
    const chartEl = document.querySelector('#chartTwentyNine');
    if (chartEl) {

        const options = {
            colors: ["#465FFF", "#9CB9FF"],
            series: [
                {
                    name: "Income",
                    data: [
                        9500, 6400, 14000, 7500, 9500, 10200, 7000, 11600, 9200, 12500, 7600,
                        6400,
                    ],
                },
                {
                    name: "Expense",
                    data: [
                        6200, 4100, 9200, 5000, 6300, 6800, 4600, 7600, 6000, 8200, 5000,
                        4100,
                    ],
                },
            ],
            chart: {
                type: "bar",
                height: 250,
                stacked: true,
                toolbar: {
                    show: false,
                },
                zoom: {
                    enabled: false,
                },
                fontFamily: "Outfit, sans-serif",
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "40%",
                    borderRadius: 6,
                    borderRadiusApplication: "end", // 'around', 'end'
                    borderRadiusWhenStacked: "last", // 'all', 'last'
                },
            },
            fill: {
                opacity: 1,
            },
            dataLabels: {
                enabled: false,
            },
            xaxis: {
                categories: [
                    "Jan",
                    "Feb",
                    "Mar",
                    "Apr",
                    "May",
                    "Jun",
                    "Jul",
                    "Aug",
                    "Sep",
                    "Oct",
                    "Nov",
                    "Dec",
                ],
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
                labels: {
                    style: {
                        colors: "#98A2B3",
                        fontSize: "12px",
                    },
                },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: "#98A2B3",
                        fontSize: "12px",
                    },
                    formatter: (value) => {
                        return value >= 1000 ? `${value / 1000}K` : value;
                    },
                },
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
                borderColor: "#E9EDF5",
                strokeDashArray: 0,
            },
            legend: {
                show: false,
            },
            tooltip: {
                enabled: true,
                x: {
                    show: false,
                },
                y: {
                    formatter: (value) => `$${value}`,
                },
            },
        };

        const chart = new ApexCharts(
            document.querySelector("#chartTwentyNine"),
            options,
        );
        chart.render();

        // Expose chart instance globally to enable custom legend interactions
        window.chart29 = chart;

        // Dark Mode Support
        const updateChartOptions = () => {
            const isDarkMode = document.documentElement.classList.contains("dark");
            chart.updateOptions({
                grid: {
                    borderColor: isDarkMode ? "#2E3545" : "#E9EDF5",
                },
            });
        };

        const observer = new MutationObserver(updateChartOptions);
        observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });
    }
}
export default initChartTwentyNine;

