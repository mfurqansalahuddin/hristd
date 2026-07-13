export function initChartThirty() {
    const chartEl = document.querySelector('#chartThirty');
    if (chartEl) {

        const getDarkMode = () => document.documentElement.classList.contains('dark');
        const options = {
            colors: ["#7592FF", "#7CD4FD", "#BDB4FE", "#FE9EFE", "#6FEAA6", "#D0D5DD"],
            series: [
                {
                    name: "Activity",
                    data: [45],
                },
                {
                    name: "Online Purchases",
                    data: [25],
                },
                {
                    name: "Groceries",
                    data: [15],
                },
                {
                    name: "Digital Goods",
                    data: [20],
                },
                {
                    name: "Stationery",
                    data: [10],
                },
                {
                    name: "Others",
                    data: [30],
                },
            ],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "bar",
                height: 50,
                stacked: true,
                toolbar: {
                    show: false,
                },
                animations: {
                    enabled: false,
                },
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: "32px",
                    borderRadius: 4,
                    borderRadiusApplication: 'all',
                    borderRadiusWhenStacked: 'all',
                },
            },
            dataLabels: {
                enabled: false,
            },
            grid: {
                show: false,
                padding: {
                    top: -30,
                    bottom: -20,
                    left: -10,
                    right: 0,
                },
            },
            xaxis: {
                labels: {
                    show: false,
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
                    show: false,
                },
            },
            legend: {
                show: false,
            },
            tooltip: {
                enabled: true,
                x: {
                    show: false,
                },
            },
            stroke: {
                show: true,
                width: 2,
                colors: getDarkMode() ? "#111827" : "#ffffff",
            },
            states: {
                hover: {
                    filter: {
                        type: "none",
                    },
                },
                active: {
                    filter: {
                        type: "none",
                    },
                },
            },
        };

        const chart = new ApexCharts(chartEl, options);
        chart.render();

        // ✅ Dark mode live update (FIXED)
        const updateChartOptions = () => {
            chart.updateOptions({
                stroke: {
                    colors: getDarkMode() ? "#111827" : "#ffffff",
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
export default initChartThirty;

