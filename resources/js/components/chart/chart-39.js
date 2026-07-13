export const initChartThirtyNine = () => {
    const el = document.querySelector("#chartThirtyNine");
    if (!el) return;

    const getOptions = (isDark) => ({
        chart: {
            type: "radar",
            height: 380,
            toolbar: { show: false },
            fontFamily: "Outfit, sans-serif",
            background: "transparent",
        },
        series: [{ name: "Weekly", data: [100, 40, 60, 25, 60, 80, 20] }],
        labels: [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
        ],
        colors: ["#465FFF"],
        fill: { opacity: 0.3 },
        stroke: { show: true, width: 2, colors: ["#465FFF"] },
        markers: { size: 0 },
        dataLabels: {
            enabled: true,
            background: {
                enabled: true,
                borderRadius: 6,
                borderWidth: 0,
                foreColor: "#465FFF",
                padding: 6,
                dropShadow: { enabled: false },
            },
            style: { fontSize: "12px", fontWeight: "600", colors: ["#ffffff"] },
            formatter: (val) => val,
        },
        plotOptions: {
            radar: {
                polygons: {
                    strokeColors: isDark ? "#313D4F" : "#E4E7EC",
                    connectorColors: isDark ? "#313D4F" : "#E4E7EC",
                    fill: {
                        colors: isDark ? ["#1e2d40", "#1a2535"] : ["#F2F4F7", "#ffffff"],
                    },
                },
            },
        },
        yaxis: {
            show: true,
            min: 0,
            max: 140,
            tickAmount: 7,
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

export default initChartThirtyNine;
