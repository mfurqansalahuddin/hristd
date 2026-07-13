
export function initChartTwentySeven() {
  const chartEl = document.querySelector('#chartTwentySeven');
  if (chartEl) {

    const darkCount = 14;
    const lightCount = 14;
    const grayCount = 14;

    const totalBars = darkCount + lightCount + grayCount;

    const colors = [
      ...Array(darkCount).fill("#465FFF"),
      ...Array(lightCount).fill("#36BFFA"),
      ...Array(grayCount).fill("#E4E7EC"),
    ];
    const chartOptions = {
      series: [
        {
          data: Array(totalBars).fill(100),
        },
      ],
      chart: {
        fontFamily: "Outfit, sans-serif",
        type: "bar",
        height: 32,
        sparkline: { enabled: true },
        toolbar: { show: false },
        animations: { enabled: false },
      },

      plotOptions: {
        bar: {
          horizontal: false,
          distributed: true,
          columnWidth: "70%",
          borderRadius: 1,
          borderRadiusApplication: "around",
        },
      },

      colors: colors,

      dataLabels: { enabled: false },

      xaxis: {
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false },
      },

      yaxis: { show: false },

      grid: {
        show: false,
        padding: { top: 0, right: 0, bottom: 0, left: 0 },
      },

      tooltip: { enabled: false },
      legend: { show: false },
    };
    const chartTwentySeven = new ApexCharts(chartEl, chartOptions);
    chartTwentySeven.render();
    return chartTwentySeven;
  }
}
export default initChartTwentySeven;

