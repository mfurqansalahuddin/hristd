export const initChartThirtySeven = () => {
    const el = document.querySelector("#chartThirtySeven");
    if (!el) return;

    const getOptions = (isDark) => ({
        chart: {
            type: "radar",
            height: 320,
            toolbar: { show: false },
            fontFamily: "Outfit, sans-serif",
            background: "transparent",
        },
        series: [
            {
                name: "Data",
                data: [9, 7, 3, 5, 3, 4, 6, 8],
            },
        ],
        labels: [
            "Estonia",
            "Germany",
            "France",
            "Spain",
            "Italy",
            "Canada",
            "Japan",
            "Brazil",
        ],
        colors: ["#465FFF"],
        fill: { opacity: 0.3 },
        stroke: { show: true, width: 3, colors: ["#465FFF"] },
        markers: {
            size: 4,
            colors: ["#465FFF"],
            strokeColors: isDark ? "#1D2939" : "#fff",
            strokeWidth: 2,
        },
        dataLabels: { enabled: false },
        plotOptions: {
            radar: {
                polygons: {
                    strokeColors: isDark ? "#313D4F" : "#E4E7EC",
                    connectorColors: isDark ? "#313D4F" : "#E4E7EC",
                    fill: {
                        colors: isDark ? ["#1e2d40", "#1a2535"] : ["#ffffff", "#ffffff"],
                    },
                },
            },
        },
        yaxis: {
            show: true,
            min: 0,
            max: 9,
            tickAmount: 3,
            labels: {
                style: { fontSize: "11px", colors: "#98A2B3" },
                formatter: (val) => val,
            },
        },
        xaxis: {
            labels: {
                style: {
                    fontSize: "13px",
                    colors: Array(8).fill(isDark ? "#98A2B3" : "#344054"),
                },
            },
        },
        legend: { show: false },
        tooltip: { y: { formatter: (val) => val } },
    });

    const isDark = () => document.documentElement.classList.contains("dark");
    const chart = new ApexCharts(el, getOptions(isDark()));
    chart.render();

    const observer = new MutationObserver(() => {
        chart.updateOptions(getOptions(isDark()));
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });
};

export default initChartThirtySeven;
