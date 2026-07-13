import jsVectorMap from "jsvectormap";
import "jsvectormap/dist/jsvectormap.min.css";
import "jsvectormap/dist/maps/world";

const globalUserMap = () => {
  const mapSelector = document.querySelector("#mapGlobalUser");

  if (!mapSelector) return;

  const map = new jsVectorMap({
    selector: "#mapGlobalUser",
    map: "world",
    zoomButtons: false,
    zoomOnScroll: false,
    zoomAnimate: true,
    zoomStep: 1.5,
    zoomMax: 12,
    zoomMin: 1,
    regionStyle: {
      initial: {
        fontFamily: "Outfit",
        fill: "#D9D9D9",
        stroke: "none",
        strokeWidth: 0,
        strokeOpacity: 0,
      },
      hover: {
        fillOpacity: 0.7,
        fill: "#465FFF",
        cursor: "pointer",
      },
      selected: {
        fill: "#465FFF",
      },
      selectedHover: {},
    },
    markers: [
      {
        name: "United States",
        coords: [37.2580397, -104.657039],
      },
      {
        name: "India",
        coords: [20.7504374, 73.7276105],
      },
      {
        name: "United Kingdom",
        coords: [53.613, -11.6368],
      },
      {
        name: "Australia",
        coords: [-25.0304388, 115.2092761],
      },
    ],
    markerStyle: {
      initial: {
        strokeWidth: 1,
        fill: "#465FFF",
        fillOpacity: 1,
        r: 5,
      },
      hover: {
        fill: "#3538CD",
        fillOpacity: 1,
        r: 7,
      },
      selected: {},
      selectedHover: {},
    },
    onRegionTooltipShow: function () {},
  });

  // Zoom in button
  const zoomInBtn = document.querySelector("#mapGlobalUserZoomIn");
  if (zoomInBtn) {
    zoomInBtn.addEventListener("click", () => {
      map._setScale(
        map.scale * map.params.zoomStep,
        map._width / 2,
        map._height / 2,
        false,
        map.params.zoomAnimate,
      );
    });
  }

  // Zoom out button
  const zoomOutBtn = document.querySelector("#mapGlobalUserZoomOut");
  if (zoomOutBtn) {
    zoomOutBtn.addEventListener("click", () => {
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

export default globalUserMap;
