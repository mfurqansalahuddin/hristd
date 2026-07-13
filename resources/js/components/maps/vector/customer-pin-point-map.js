import jsVectorMap from "jsvectormap";
import "jsvectormap/dist/jsvectormap.min.css";
import "./us-aea-en";

const customerPinPointMap = () => {
  const el = document.querySelector("#mapCustomerPinPoint");
  if (!el) return;

  const map = new jsVectorMap({
    selector: "#mapCustomerPinPoint",
    map: "us_aea_en",
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
        strokeWidth: 2,
        strokeOpacity: 1,
      },
      hover: {
        fillOpacity: 0.8,
        cursor: "pointer",
        fill: "#465FFF",
        stroke: "none",
      },
      selected: {
        fill: "#465FFF",
      },
      selectedHover: {},
    },
    markers: [
      { name: "Los Angeles", coords: [34.05, -118.24] },
      { name: "New York", coords: [40.71, -74.0], style: { r: 8 } },
      { name: "Chicago", coords: [41.87, -87.62] },
      { name: "Houston", coords: [29.76, -95.36] },
      { name: "Denver", coords: [39.73, -104.99] },
      { name: "Seattle", coords: [47.6, -122.33] },
      { name: "Miami", coords: [25.76, -80.19] },
      { name: "Atlanta", coords: [33.74, -84.38] },
      { name: "Philadelphia", coords: [39.95, -75.16] },
      { name: "Boston", coords: [42.36, -71.05] },
      { name: "Nashville", coords: [36.16, -86.78] },
      { name: "Dallas", coords: [32.77, -96.79] },
      { name: "Minneapolis", coords: [44.97, -93.26] },
      { name: "Washington D.C.", coords: [38.9, -77.03], style: { r: 6 } },
      { name: "San Francisco", coords: [37.77, -122.41] },
      { name: "Las Vegas", coords: [36.1, -115.17] },
      { name: "Phoenix", coords: [33.44, -112.07] },
      { name: "San Antonio", coords: [29.42, -98.49] },
    ],
    markerStyle: {
      initial: {
        fill: "#465FFF",
        stroke: "white",
        strokeWidth: 2,
        r: 5,
      },
      hover: {
        fill: "#3538CD",
        r: 6,
      },
      selected: {},
      selectedHover: {},
    },
    onRegionTooltipShow: function () {},
  });

  const zoomIn = document.querySelector("#mapCustomerZoomIn");
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

  const zoomOut = document.querySelector("#mapCustomerZoomOut");
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

export default customerPinPointMap;
