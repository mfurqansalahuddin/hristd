import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const officeLocationEditor = () => {
    const container = document.querySelector('#officeLocationMap');
    if (!container) return;

    // Fix Leaflet default icon paths broken by webpack/vite
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
        iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
        shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
    });

    const latInput = document.querySelector('#location-lat');
    const longInput = document.querySelector('#location-long');
    const radiusInput = document.querySelector('#location-radius');
    const polygonInput = document.querySelector('#location-polygon');
    const polygonCountEl = document.querySelector('#polygon-point-count');
    const typeRadios = document.querySelectorAll('input[name="type"]');

    // Default: pusat Kota Banda Aceh, dipakai kalau belum ada koordinat tersimpan.
    const initialLat = parseFloat(latInput.value) || 5.5483;
    const initialLong = parseFloat(longInput.value) || 95.3238;

    const map = L.map('officeLocationMap', { center: [initialLat, initialLong], zoom: 15 });

    const streets = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);
    const satellite = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        { attribution: 'Tiles &copy; Esri' },
    );
    // Tombol jenis peta — custom (bukan L.control.layers bawaan) biar ukurannya kecil (34x34)
    // dan bukanya jadi panel kecil, konsisten dengan tampilan di app mobile.
    const layersControl = new L.Control({ position: 'topright' });
    layersControl.onAdd = () => {
        const wrapper = L.DomUtil.create('div');
        wrapper.style.position = 'relative';

        const button = L.DomUtil.create('button', 'leaflet-bar', wrapper);
        button.type = 'button';
        button.title = 'Jenis peta';
        button.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg>';
        button.style.width = '34px';
        button.style.height = '34px';
        button.style.display = 'flex';
        button.style.alignItems = 'center';
        button.style.justifyContent = 'center';
        button.style.background = '#fff';
        button.style.border = 'none';
        button.style.cursor = 'pointer';
        button.style.color = '#333';

        const panel = L.DomUtil.create('div', '', wrapper);
        panel.style.cssText =
            'display:none;position:absolute;top:38px;right:0;min-width:140px;padding:8px;border-radius:8px;background:#fff;box-shadow:0 1px 5px rgba(0,0,0,.4);font-family:inherit;';
        panel.innerHTML = `
            <div style="font-size:12px;font-weight:600;color:#333;margin-bottom:6px;">Jenis Peta</div>
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#333;padding:4px 0;cursor:pointer;">
                <input type="radio" name="basemap" value="streets" checked /> Default
            </label>
            <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:#333;padding:4px 0;cursor:pointer;">
                <input type="radio" name="basemap" value="satellite" /> Satelit
            </label>
        `;

        L.DomEvent.disableClickPropagation(wrapper);
        L.DomEvent.on(button, 'click', () => {
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        });
        panel.querySelectorAll('input[type="radio"]').forEach((input) => {
            input.addEventListener('change', (event) => {
                if (event.target.value === 'satellite') {
                    map.removeLayer(streets);
                    satellite.addTo(map);
                } else {
                    map.removeLayer(satellite);
                    streets.addTo(map);
                }
            });
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) panel.style.display = 'none';
        });

        return wrapper;
    };
    layersControl.addTo(map);

    let marker = null;
    let circle = null;
    let polygonLayer = null;
    let polygonPoints = [];

    try {
        polygonPoints = JSON.parse(polygonInput.value || '[]');
    } catch {
        polygonPoints = [];
    }

    const currentType = () => [...typeRadios].find((radio) => radio.checked)?.value || 'RADIUS';

    const updatePolygonCount = () => {
        if (polygonCountEl) polygonCountEl.textContent = `${polygonPoints.length} titik`;
    };

    const renderMarker = (lat, lng) => {
        if (marker) {
            marker.setLatLng([lat, lng]);
            return;
        }
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', (event) => {
            const position = event.target.getLatLng();
            setCenter(position.lat, position.lng);
        });
    };

    const renderCircle = (lat, lng, radius) => {
        if (circle) {
            circle.setLatLng([lat, lng]);
            circle.setRadius(radius);
            return;
        }
        circle = L.circle([lat, lng], { radius, color: '#465fff', fillOpacity: 0.15 }).addTo(map);
    };

    const removeCircle = () => {
        if (circle) {
            map.removeLayer(circle);
            circle = null;
        }
    };

    const renderPolygon = () => {
        if (polygonLayer) {
            map.removeLayer(polygonLayer);
            polygonLayer = null;
        }
        if (polygonPoints.length > 0) {
            polygonLayer = L.polygon(polygonPoints.map((point) => [point.lat, point.lng]), {
                color: '#465fff',
                fillOpacity: 0.15,
            }).addTo(map);
        }
    };

    const setCenter = (lat, lng) => {
        latInput.value = lat.toFixed(8);
        longInput.value = lng.toFixed(8);
        renderMarker(lat, lng);
        if (currentType() === 'RADIUS') {
            renderCircle(lat, lng, parseInt(radiusInput.value || 100, 10));
        }
    };

    const syncModeVisuals = () => {
        if (currentType() === 'RADIUS') {
            if (polygonLayer) {
                map.removeLayer(polygonLayer);
                polygonLayer = null;
            }
            const center = marker?.getLatLng();
            if (center) renderCircle(center.lat, center.lng, parseInt(radiusInput.value || 100, 10));
        } else {
            removeCircle();
            renderPolygon();
        }
    };

    if (latInput.value && longInput.value) {
        renderMarker(initialLat, initialLong);
        if (currentType() === 'RADIUS') {
            renderCircle(initialLat, initialLong, parseInt(radiusInput.value || 100, 10));
        }
    }
    if (polygonPoints.length > 0) {
        renderPolygon();
        updatePolygonCount();
    }

    if (radiusInput) {
        radiusInput.addEventListener('input', () => {
            if (circle) circle.setRadius(parseInt(radiusInput.value || 0, 10));
        });
    }

    typeRadios.forEach((radio) => radio.addEventListener('change', syncModeVisuals));

    map.on('click', (event) => {
        const { lat, lng } = event.latlng;

        if (currentType() === 'POLYGON') {
            polygonPoints.push({ lat, lng });
            polygonInput.value = JSON.stringify(polygonPoints);
            renderPolygon();
            updatePolygonCount();
            renderMarker(lat, lng);
            latInput.value = lat.toFixed(8);
            longInput.value = lng.toFixed(8);
        } else {
            setCenter(lat, lng);
        }
    });

    const undoBtn = document.querySelector('#polygon-undo');
    if (undoBtn) {
        undoBtn.addEventListener('click', () => {
            polygonPoints.pop();
            polygonInput.value = JSON.stringify(polygonPoints);
            renderPolygon();
            updatePolygonCount();
        });
    }

    const resetBtn = document.querySelector('#polygon-reset');
    if (resetBtn) {
        resetBtn.addEventListener('click', () => {
            polygonPoints = [];
            polygonInput.value = '[]';
            renderPolygon();
            updatePolygonCount();
        });
    }
};

export default officeLocationEditor;
