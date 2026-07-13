import maplibregl from "maplibre-gl";
import "maplibre-gl/dist/maplibre-gl.css";

const locationMap3 = () => {
  const el = document.querySelector("#mapLocationView3");
  if (!el) return;

  const map = new maplibregl.Map({
    container: "mapLocationView3",
    style: "https://tiles.openfreemap.org/styles/bright",
    center: [-77.0369, 38.9072], // Washington D.C. [lng, lat]
    zoom: 8.5,
    scrollZoom: false,
    attributionControl: false,
  });

  // Custom zoom controls — keyboard accessible
  const zoomIn = document.querySelector("#mapLocationZoomIn3");
  const zoomOut = document.querySelector("#mapLocationZoomOut3");
  if (zoomIn) {
    zoomIn.setAttribute("type", "button");
    zoomIn.addEventListener("click", () => map.zoomIn());
  }
  if (zoomOut) {
    zoomOut.setAttribute("type", "button");
    zoomOut.addEventListener("click", () => map.zoomOut());
  }
};

export default locationMap3;
