import jsVectorMap from "jsvectormap";
import "jsvectormap/dist/jsvectormap.min.css";
import "jsvectormap/dist/maps/world";

const trafficAnalyticsMap = () => {
  const el = document.querySelector("#mapTrafficAnalytics");
  if (!el) return;

  const map = new jsVectorMap({
    selector: "#mapTrafficAnalytics",
    map: "world",
    zoomButtons: false,
    zoomOnScroll: false,
    zoomAnimate: true,
    zoomStep: 1.5,
    zoomMax: 12,
    zoomMin: 1,
    backgroundColor: "transparent",
    regionStyle: {
      initial: {
        fill: "#C5D8FF",
        fillOpacity: 1,
        stroke: "white",
        strokeWidth: 0.5,
        strokeOpacity: 1,
      },
      hover: {
        fillOpacity: 0.8,
        cursor: "pointer",
        fill: "#465FFF",
      },
      selected: {
        fill: "#3538CD",
      },
      selectedHover: {},
    },
    onRegionTooltipShow: function () {},
  });

  // Zoom in
  const zoomIn = document.querySelector("#mapTrafficZoomIn");
  if (zoomIn) {
    zoomIn.addEventListener("click", () => {
      map._setScale(
        map.scale * map.params.zoomStep,
        map._width / 2,
        map._height / 2,
        false,
        map.params.zoomAnimate,
      );
    });
  }

  // Zoom out
  const zoomOut = document.querySelector("#mapTrafficZoomOut");
  if (zoomOut) {
    zoomOut.addEventListener("click", () => {
      map._setScale(
        map.scale / map.params.zoomStep,
        map._width / 2,
        map._height / 2,
        false,
        map.params.zoomAnimate,
      );
    });
  }
};

export default trafficAnalyticsMap;
