
export function initChartTwentyFive() {
    // const chartEl = document.querySelector('#chartTwentyFive');
    // if (chartEl) {
    //     const chartOptions = {

    //     };
    //     const chartTwentyFive = new ApexCharts(chartEl, chartOptions);
    //     chartTwentyFive.render();
    //     return chartTwentyFive;
    // }

    const getChartOptions = (color) => ({
        series: [
            {
                name: "New Sales",
                data: [300, 350, 310, 370, 248, 187, 295, 191, 269, 201, 185, 252, 151],
            },
        ],
        grid: {
            show: false,
        },
        colors: [color],
        fill: {
            gradient: {
                enabled: true,
                opacityFrom: 0.55,
                opacityTo: 0,
            },
        },
        legend: {
            show: false,
        },
        chart: {
            fontFamily: "Outfit, sans-serif",
            height: 70,
            type: "area",
            parentHeightOffset: 0,
            toolbar: {
                show: false,
            },
        },
        tooltip: {
            enabled: false,
        },
        dataLabels: {
            enabled: false,
        },
        stroke: {
            curve: "smooth",
            width: 1,
        },
        xaxis: {
            type: "datetime",
            categories: [
                "2018-09-19T00:00:00.000Z",
                "2018-09-19T01:30:00.000Z",
                "2018-09-19T02:30:00.000Z",
                "2018-09-19T03:30:00.000Z",
                "2018-09-19T04:30:00.000Z",
                "2018-09-19T05:30:00.000Z",
                "2018-09-19T06:30:00.000Z",
                "2018-09-19T07:30:00.000Z",
                "2018-09-19T08:30:00.000Z",
                "2018-09-19T09:30:00.000Z",
                "2018-09-19T10:30:00.000Z",
                "2018-09-19T11:30:00.000Z",
                "2018-09-19T12:30:00.000Z",
            ],
            labels: {
                show: false,
            },
            axisBorder: {
                show: false,
            },
            axisTicks: {
                show: false,
            },
            tooltip: {
                enabled: false,
            },
        },
        yaxis: {
            labels: {
                show: false,
            },
        },
    });

    const chartSelector = document.querySelectorAll(".chartTwentyFive");

    if (chartSelector.length) {
        const chartTwentyFive01 = new ApexCharts(
            document.querySelector(".chartTwentyFive-01"),
            getChartOptions("#12B76A"),
        );
        chartTwentyFive01.render();

        const chartTwentyFive02 = new ApexCharts(
            document.querySelector(".chartTwentyFive-02"),
            getChartOptions("#7C3AED"),
        );
        chartTwentyFive02.render();

        const chartTwentyFive03 = new ApexCharts(
            document.querySelector(".chartTwentyFive-03"),
            getChartOptions("#38BDF8"),
        );
        chartTwentyFive03.render();

        const chartTwentyFive04 = new ApexCharts(
            document.querySelector(".chartTwentyFive-04"),
            getChartOptions("#F04438"),
        );
        chartTwentyFive04.render();

        return [
            chartTwentyFive01,
            chartTwentyFive02,
            chartTwentyFive03,
            chartTwentyFive04,
        ];
    }
}
export default initChartTwentyFive;
