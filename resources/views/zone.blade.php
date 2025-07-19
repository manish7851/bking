<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Real-Time GPS Tracker (URL Config)</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
html, body { height: 100%; margin: 0; }
#container {
  display: flex;
  height: 100%;
  overflow: hidden;
}

#controls {
  width: 350px;
  padding: 10px;
  background: #fff;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
  font-family: sans-serif;
  overflow-y: auto;
  box-sizing: border-box;
  transition: all 0.3s ease;
}

#controls.hidden {
  display: none;
}

#map {
  flex: 1;
  height: 100%;
  width: 100%; /* needed for flex fallback */
  transition: all 0.3s ease;
}

#toggle-btn {
  position: absolute;
  left: 0;
  top: 10px;
  z-index: 1000;
  padding: 6px 12px;
  cursor: pointer;
  background: #333;
  color: #fff;
  border: none;
}
input, button {
  width: 100%;
  padding: 8px;
  margin-bottom: 10px;
  box-sizing: border-box;
}
#map { flex: 1; height: 100%; }
#vehicle-status {
  font-size: 12px;
  margin-top: 10px;
}
.highlight { color: green; font-weight: bold; }
#notification {
  position: absolute;
  top: 20px;
  right: 20px;
  color: red;
  padding: 12px 18px;
  border-radius: 8px;
  font-size: 20px; /* larger font */
  max-width: 400px;
  max-height: 40px;
  z-index: 1000;
  display: none;
}
 #status {
    position: absolute;
    bottom: 20px;
    right: 20px;
    padding: 12px 18px;
    border-radius: 8px;
    font-size: 20px; /* larger font */
    max-width: 400px;
    max-height: 40px;
    z-index: 1000;
  }
  .dot {
    height: 12px;
    width: 12px;
    border-radius: 50%;
    margin-right: 8px;
    background-color: gray;
  }
  .connected { background-color: green; }
  .disconnected { background-color: red; }
  .connecting { background-color: orange; }
</style>
</head>
<body>

<div id="container">
	<button id="toggleSidebar" style="position: absolute; top: 10px; left: 10px; z-index: 1001; width: 100px;">
  Hide Sidebar
</button>
  <div id="controls">
	<br/>
	<br/>
    <label>IMEI:</label>
    <input id="imei" readonly />

    <label>Start Address:</label>
    <input id="start-addr" readonly />

    <label>Destination Address:</label>
    <input id="dest-addr" readonly />

    <label>Source Exit Radius (km):</label>
    <input id="radius" type="number" min="0.01" step="0.01" value="0.2"/>

    <label>Destination Entry Radius (km):</label>
    <input id="dest-radius" type="number" min="0.01" step="0.01" value="0.2"/>

    <label>Alert Zone Address:</label>
    <input id="zone-addr" />
    <div>
    <input type="checkbox" id="pickOnMap" style="display: inline; width: auto;" /><span>Pick Location on Map</span>
    </div>
    <label>Alert Zone Lat:</label>
    <input id="zone-lat" />

    <label>Alert Zone Lon:</label>
    <input id="zone-lon" />

    <label>Alert Zone Radius (km):</label>
    <input id="zone-radius" type="number" min="0.01" step="0.01" value="0.2" />

    <button onclick="drawCirclesAndMarkers()">Reapply Changes</button>

    <div id="vehicle-status">Status: Idle.</div>
  </div>
  <div id="map"></div>
  <div id="notification"></div>
  <div id="status">
    <div class="dot" id="status-dot"></div>
    <span id="status-text">Connecting...</span>
  </div>
</div>
<script>
window.serverData = {
  imei: @json($imei),
  source: @json($source),
  destination: @json($destination),
  source_latitude: @json($source_latitude),
  source_longitude: @json($source_longitude),
  destination_latitude: @json($destination_latitude),
  destination_longitude: @json($destination_longitude)
};

document.getElementById('imei').value = window.serverData.imei || '';
document.getElementById('start-addr').value = window.serverData.source || '';
document.getElementById('dest-addr').value = window.serverData.destination || '';
document.getElementById('zone-lat').value = window.serverData.source_latitude || '';
document.getElementById('zone-lon').value = window.serverData.source_longitude || '';
document.getElementById('zone-addr').value = window.serverData.source || '';

console.log(window.serverData);
const map = L.map('map').setView([window.serverData.source_latitude, window.serverData.source_longitude], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);
let marker = L.marker([28.132846666666666, 82.29789333333333], { draggable: true }).addTo(map);
let routePaths;
let startCircle, destCircle, zoneCircle;
let startMarker, destMarker, zoneMarker;
const vehicleMarker = {};

// Sidebar toggle
function toggleSidebar() {
  const controls = document.getElementById('controls');
  controls.classList.toggle('collapsed');
  document.getElementById('toggle-btn').textContent = controls.classList.contains('collapsed') ? 'Show Sidebar' : 'Hide Sidebar';
}

function drawCirclesAndMarkers() {
  const sLat = parseFloat(window.serverData.source_latitude);
  const sLon = parseFloat(window.serverData.source_longitude);
  const dLat = parseFloat(window.serverData.destination_latitude);
  const dLon = parseFloat(window.serverData.destination_longitude);
  const zLat = parseFloat(document.getElementById('zone-lat').value);
  const zLon = parseFloat(document.getElementById('zone-lon').value);

  const r = parseFloat(document.getElementById('radius').value) * 1000;
  const dr = parseFloat(document.getElementById('dest-radius').value) * 1000;
  const zr = parseFloat(document.getElementById('zone-radius').value) * 1000;

  if (!isNaN(sLat) && !isNaN(sLon)) {
    if (startCircle) map.removeLayer(startCircle);
    if (startMarker) map.removeLayer(startMarker);
    startCircle = L.circle([sLat, sLon], { radius: r, color: 'green' }).addTo(map);
    startMarker = L.marker([sLat, sLon]).addTo(map).bindPopup('Start Point');
  }

  if (!isNaN(dLat) && !isNaN(dLon)) {
    if (destCircle) map.removeLayer(destCircle);
    if (destMarker) map.removeLayer(destMarker);
    destCircle = L.circle([dLat, dLon], { radius: dr, color: 'red' }).addTo(map);
    destMarker = L.marker([dLat, dLon]).addTo(map).bindPopup('Destination');
  }

  if (!isNaN(zLat) && !isNaN(zLon)) {
    if (zoneCircle) map.removeLayer(zoneCircle);
    if (zoneMarker) map.removeLayer(zoneMarker);
    zoneCircle = L.circle([zLat, zLon], { radius: zr, color: 'orange' }).addTo(map);
    zoneMarker = L.marker([zLat, zLon]).addTo(map).bindPopup('Alert Zone');
  }
}

function updateVehicleStatus(msg, highlight = false) {
  document.getElementById('vehicle-status').innerHTML = highlight ? `<span class="highlight">${msg}</span>` : msg;
}

function calculateDistance(lat1, lon1, lat2, lon2) {
  const R = 6371;
  const dLat = (lat2 - lat1) * Math.PI / 180;
  const dLon = (lon2 - lon1) * Math.PI / 180;
  const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLon / 2) ** 2;
  return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}
const statusDot = document.getElementById('status-dot');
const statusText = document.getElementById('status-text');

function updateStatus(state, text) {
  statusDot.className = 'dot ' + state;
  statusText.textContent = text;
}

updateStatus('connecting', 'Connecting...');

let ws = new WebSocket('ws://103.90.84.153:8081');

ws.onopen = () => {
  updateStatus('connected', 'Connected');
};

ws.onclose = (event) => {
  updateStatus('disconnected', 'Disconnected');
};

ws.onerror = (error) => {
  updateStatus('disconnected', 'Connection Error');
  console.error('[WS] Error:', error);
};

ws.onmessage = function (event) {
  const data = JSON.parse(event.data);
  if (!data?.data) return;

  const { imei, lat, lon, speed, datetime } = data.data;
  if (imei !== document.getElementById('imei').value) return;

  if (!vehicleMarker.marker) {
    console.log('Adding vehicle marker', imei, lat, lon);
    vehicleMarker.marker = L.marker([lat, lon]).addTo(map);
    map.setView([lat, lon], 15);
  } else {
    console.log('Updating vehicle marker', imei, lat, lon);
    vehicleMarker.marker.setLatLng([lat, lon]);
  }

  updateVehicleStatus(`IMEI: ${imei}<br>Lat: ${lat.toFixed(5)}, Lon: ${lon.toFixed(5)}<br>Speed: ${speed} km/h<br>Time: ${datetime}`);

  const sLat = parseFloat(window.serverData.source_latitude);
  const sLon = parseFloat(window.serverData.source_longitude);
  const dLat = parseFloat(window.serverData.destination_latitude);
  const dLon = parseFloat(window.serverData.destination_longitude);
  const zLat = parseFloat(document.getElementById('zone-lat').value);
  const zLon = parseFloat(document.getElementById('zone-lon').value);

  const distFromStart = calculateDistance(lat, lon, sLat, sLon);
  const distToDest = calculateDistance(lat, lon, dLat, dLon);
  const distToZone = calculateDistance(lat, lon, zLat, zLon);

  if (distFromStart > parseFloat(document.getElementById('radius').value)) {
	  showNotification(`🚨 Exited source radius!`);
	}

	if (distToDest < parseFloat(document.getElementById('dest-radius').value)) {
	  showNotification(`✅ Arrived at destination!`);
	}

	if (distToZone < parseFloat(document.getElementById('zone-radius').value)) {
	  showNotification(`⚠️ Entered alert zone!`);
	}

};
function showNotification(message, duration = 30000) {
  const notification = document.getElementById('notification');
  notification.innerHTML = message;
  notification.style.display = 'block';
  clearTimeout(notification._timeout);
  notification._timeout = setTimeout(() => {
    notification.style.display = 'none';
  }, duration);
}
document.getElementById('toggleSidebar').addEventListener('click', function () {
  const sidebar = document.getElementById('controls');
  const mapContainer = document.getElementById('map');
  const btn = document.getElementById('toggleSidebar');

  if (sidebar.classList.contains('hidden')) {
    sidebar.classList.remove('hidden');
    btn.textContent = 'Hide Sidebar';
  } else {
    sidebar.classList.add('hidden');
    btn.textContent = 'Show Sidebar';
  }

  setTimeout(() => {
    map.invalidateSize(); // Ensures map resizes correctly
  }, 300);
});

drawCirclesAndMarkers();

// marker.on('dragend', () => updateAddress(marker.getLatLng()));

map.on('click', e => {
  let selectedAddr;
  if(document.getElementById("pickOnMap").checked) {
    marker.setLatLng(e.latlng);
    document.getElementById('zone-lat').value = e.latlng.lat.toFixed(6);
    document.getElementById('zone-lon').value = e.latlng.lng.toFixed(6);
    if (zoneCircle) map.removeLayer(zoneCircle);
    if (zoneMarker) map.removeLayer(zoneMarker);
    const zLat = parseFloat(document.getElementById('zone-lat').value);
    const zLon = parseFloat(document.getElementById('zone-lon').value);

    const zr = parseFloat(document.getElementById('zone-radius').value) * 1000;
    zoneCircle = L.circle([zLat, zLon], { radius: zr, color: 'orange' }).addTo(map);
    zoneMarker = L.marker([zLat, zLon]).addTo(map).bindPopup('Alert Zone');
    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${e.latlng.lat}&lon=${e.latlng.lng}`)
    .then(res => res.json())
    .then(data => {
      selectedAddr = data.display_name || 'Unknown location';
      document.getElementById('zone-addr').value = selectedAddr;
    })
    .catch(() => {
      selectedAddr = 'Unknown location';
      document.getElementById('zone-addr').value = selectedAddr;
    });
  }
});

// Simple autocomplete
const addressInput = document.getElementById('zone-addr');
addressInput.addEventListener('input', function() {
  const query = this.value;
  if (query.length < 3) return;
  fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ' Nepal')}`)
    .then(res => res.json())
    .then(data => {
      if (data[0]) {
        document.getElementById('zone-lat').value = data[0].lat;
        document.getElementById('zone-lon').value = data[0].lon;
      }
      showSuggestions(data.map(item => item.display_name));
    });
});

// Simple suggestions dropdown
let suggestionBox;
function showSuggestions(suggestions) {
  if (!suggestionBox) {
    suggestionBox = document.createElement('div');
    suggestionBox.style.position = 'absolute';
    suggestionBox.style.background = '#fff';
    suggestionBox.style.border = '1px solid #ccc';
    suggestionBox.style.maxHeight = '150px';
    suggestionBox.style.overflowY = 'auto';
    suggestionBox.style.zIndex = 999;
    document.body.appendChild(suggestionBox);
  }

  const rect = addressInput.getBoundingClientRect();
  suggestionBox.style.left = `${rect.left}px`;
  suggestionBox.style.top = `${rect.bottom + window.scrollY}px`;
  suggestionBox.style.width = `${rect.width}px`;

  suggestionBox.innerHTML = suggestions.map(s => `<div style="padding:5px;cursor:pointer">${s}</div>`).join('');
  suggestionBox.querySelectorAll('div').forEach(div => {
    div.addEventListener('click', () => {
      addressInput.value = div.textContent;
      suggestionBox.innerHTML = '';
      fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(div.textContent)}`)
        .then(res => res.json())
        .then(data => {
          if (data[0]) {
            document.getElementById('zone-lat').value = data[0].lat;
            document.getElementById('zone-lon').value = data[0].lon;
          }
        });
    });
  });
}

document.addEventListener('click', (e) => {
  if (suggestionBox && !suggestionBox.contains(e.target) && e.target !== addressInput) {
    suggestionBox.innerHTML = '';
  }
});

 function fetchRoute() {
  const slat = parseFloat(window.serverData.source_latitude);
  const slon = parseFloat(window.serverData.source_longitude);
  const dlat = parseFloat(window.serverData.destination_latitude);
  const dlon = parseFloat(window.serverData.destination_longitude);
  const zlat = parseFloat(document.getElementById('zone-lat').value);
  const zlon = parseFloat(document.getElementById('zone-lon').value);
    if (isNaN(slat) || isNaN(dlat)) return alert("Please select valid suggestions for start/destination.");

    fetch(`https://router.project-osrm.org/route/v1/driving/${slon},${slat};${dlon},${dlat}?overview=full&geometries=geojson`)
      .then(r => r.json())
      .then(data => {
        if (!data.routes?.[0]) return alert("No route found.");
        const coords = data.routes[0].geometry.coordinates.map(p => [p[1], p[0]]);
        if (routePaths) map.removeLayer(routePaths[tabIndex]);
        routePaths = L.polyline(coords, { color: 'blue' }).addTo(map);
        map.fitBounds(routePaths.getBounds());
      });
  }
  fetchRoute();
</script>
</body>
</html>
