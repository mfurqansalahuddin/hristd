export const initChartFortyThree = () => {
    const el = document.querySelector("#chartFortyThree");
    if (!el) return;

    // Strip ApexCharts' own tooltip border/bg so our inner div is the only styled wrapper
    const styleEl = document.createElement("style");
    styleEl.textContent = `
    #chartFortyThree .apexcharts-tooltip {
      background: transparent !important;
      border: none !important;
      box-shadow: none !important;
      padding: 0 !important;
    }
  `;
    document.head.appendChild(styleEl);

    const dataLabels = ["Loans", "Mortgage", "Savings", "Credit Card"];
    const dataValues = [20, 20, 20, 20];

    const options = {
        chart: {
            type: "radialBar",
            height: 320,
            toolbar: { show: false },
            fontFamily: "Outfit, sans-serif",
        },
        series: [80, 80, 80, 80],
        colors: ["#161950", "#252DAE", "#465FFF", "#9CB9FF"],
        plotOptions: {
            radialBar: {
                startAngle: 0,
                endAngle: 360,
                hollow: {
                    margin: 0,
                    size: "45%",
                    background: "transparent",
                },
                track: {
                    show: true,
                    background: "#F2F4F7",
                    strokeWidth: "100%",
                    margin: 0,
                },
                dataLabels: {
                    show: false,
                },
            },
        },
        labels: dataLabels,
        stroke: {
            lineCap: "butt",
        },
        legend: {
            show: true,
            position: "left",
            verticalAlign: "middle",
            floating: false,
            markers: {
                shape: "circle",
                size: 6,
                offsetX: -2,
                strokeWidth: 0,
            },
            formatter: (seriesName, opts) => {
                const pct = dataValues[opts.seriesIndex];
                return `${seriesName} &nbsp;&nbsp; <strong>${pct}%</strong>`;
            },
            itemMargin: {
                vertical: 6,
            },
            labels: {
                colors: "#344054",
            },
            fontSize: "13px",
        },
        tooltip: {
            enabled: true,
            custom: ({ seriesIndex, w }) => {
                const label = w.globals.labels[seriesIndex];
                const pct = dataValues[seriesIndex];
                const color = w.globals.colors[seriesIndex];
                return `<div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800" style="font-family: Outfit, sans-serif; font-size: 13px;">
          <span style="width:10px;height:10px;border-radius:50%;background:${color};display:inline-block;flex-shrink:0;"></span>
          <span class="text-gray-600 dark:text-gray-400">${label}:</span>
          <strong class="text-gray-900 dark:text-white">${pct}%</strong>
        </div>`;
            },
        },
    };

    const chart = new ApexCharts(el, options);
    chart.render();
};

export default initChartFortyThree;
