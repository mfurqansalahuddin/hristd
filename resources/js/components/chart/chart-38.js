export const initChartThirtyEight = () => {
    const el = document.querySelector("#chartThirtyEight");
    if (!el) return;

    const getOptions = (isDark) => ({
        chart: {
            type: "radar",
            height: 380,
            toolbar: { show: false },
            fontFamily: "Outfit, sans-serif",
            background: "transparent",
        },
        series: [
            { name: "Desktop", data: [70, 55, 40, 30, 10, 5, 60] },
            { name: "Mobile", data: [55, 40, 50, 60, 15, 35, 45] },
        ],
        labels: ["January", "February", "March", "April", "May", "June", "July"],
        colors: ["#3641F5", "#EE46BC"],
        fill: { opacity: 0.2 },
        stroke: { show: true, width: 2, colors: ["#465FFF", "#F05FB5"] },
        markers: { size: 0 },
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
            max: 90,
            tickAmount: 9,
            labels: {
                style: { fontSize: "11px", colors: "#98A2B3" },
                formatter: (val) => val,
            },
        },
        xaxis: {
            labels: {
                style: {
                    fontSize: "13px",
                    colors: Array(7).fill(isDark ? "#98A2B3" : "#344054"),
                },
            },
        },
        legend: {
            show: true,
            position: "bottom",
            horizontalAlign: "center",
            markers: { shape: "circle", size: 6, strokeWidth: 0, offsetX: -2 },
            itemMargin: { horizontal: 12, vertical: 0 },
            labels: { colors: isDark ? "#98A2B3" : "#344054" },
            fontSize: "14px",
        },
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

export default initChartThirtyEight;
