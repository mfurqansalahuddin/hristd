

export function initChartTwentyThree() {
  const chartEl = document.querySelector('#chartTwentyThree');
  if (chartEl) {
    const chartOptions = {
      series: [
        {
          name: "Users",
          data: [
            23000, 24500, 22500, 24800, 23200, 25240, 24600, 27000, 25800, 28500,
            27200, 31000,
          ],
        },
        {
          name: "Revenue",
          data: [
            13000, 14300, 12600, 14800, 13400, 15500, 14700, 17000, 16000, 18500,
            17200, 20000,
          ],
        },
      ],
      legend: {
        show: true,
        position: "top",
        horizontalAlign: "left",
        markers: {
          size:5,
          radius: 50,
          strokeWidth: 0,
        },
        itemMargin: {
          horizontal: 10,
        },
      },
      colors: ["#465FFF", "#9CB9FF"],
      chart: {
        fontFamily: "Outfit, sans-serif",
        height: 440,
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
        curve: "straight",
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
            formatter: (val) => "$" + (val / 1000).toFixed(2) + "K",
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
        type: "category",
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
        tooltip: false,
      },
      yaxis: {
        min: 0,
        max: 35000,
        tickAmount: 7,
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
    const chartTwentyThree = new ApexCharts(chartEl, chartOptions);
    chartTwentyThree.render();
    return chartTwentyThree;
  }
}
export default initChartTwentyThree;
