
export function initChartTwentyFour() {
    const chartEl = document.querySelector('#chartTwentyFour');
    if (chartEl) {
        const chartOptions = {
            series: [900, 700, 850],
            colors: ["#7592FF", "#7CD4FD", "#BDB4FE"],
            labels: ["Chat GPT", "Gemini Pro", "Grok"],
            chart: {
                fontFamily: "Outfit, sans-serif",
                type: "donut",
                width: 250,
                height: 250,
            },
            stroke: {
                show: false,
                width: 4, // Creates a gap between the series
                colors: "transparent", // Gap color (use background color to make it seamless)
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        background: 'transparent',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                offsetY: 0,
                                color: '#1D2939',
                                fontSize: '12px',
                                fontWeight: 'normal',
                            },
                            value: {
                                show: true,
                                offsetY: 10,
                                color: '#1D2939',
                                fontSize: '12px',
                                fontWeight: 600,
                                formatter: (val) => val,
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: '13.5M',
                                fontSize: '24px',
                                fontWeight: 600,
                                color: '#1D2939',
                                formatter: () => '2450',
                            },
                        },
                    },
                },
            },
            dataLabels: {
                enabled: false,
            },

            tooltip: {
                enabled: true,
                custom: function ({ series, seriesIndex, w }) {
                    return (
                        '<div class="rounded-lg border border-gray-200 bg-white p-2 shadow-sm dark:border-gray-800 dark:bg-gray-900">' +
                        '<div class="flex items-center gap-2">' +
                        ' <div class="size-2 rounded-full" style="background-color: ' +
                        w.config.colors[seriesIndex] +
                        '"></div>' +
                        ' <span class="text-xs font-medium text-gray-800 dark:text-white/90">' +
                        w.config.labels[seriesIndex] +
                        "</span>" +
                        "</div>" +
                        '<div class="mt-1 text-xs text-gray-500 dark:text-gray-400">' +
                        series[seriesIndex] +
                        " Units" +
                        "</div>" +
                        "</div>"
                    );
                },
            },



            legend: {
                show: false,
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: {
                            width: 280,
                            height: 280,
                        },
                    },
                },
                {
                    breakpoint: 2600,
                    options: {
                        chart: {
                            width: 240,
                            height: 240,
                        },
                    },
                },
            ],
        };
        const chartTwentyFour = new ApexCharts(chartEl, chartOptions);
        chartTwentyFour.render();
        return chartTwentyFour;
    }
}
export default initChartTwentyFour;
