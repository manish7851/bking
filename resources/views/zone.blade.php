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
    <input id="radius" type="number" min="0.01" step="0.01" value="0.5"/>

    <label>Destination Entry Radius (km):</label>
    <input id="dest-radius" type="number" min="0.01" step="0.01" value="0.5"/>

    <label>Alert Zone Address:</label>
    <input id="zone-addr" />

    <label>Alert Zone Lat:</label>
    <input id="zone-lat" />

    <label>Alert Zone Lon:</label>
    <input id="zone-lon" />

    <label>Alert Zone Radius (km):</label>
    <input id="zone-radius" type="number" min="0.01" step="0.01" value="0.5" />

    <button onclick="drawCirclesAndMarkers()">Reapply Changes</button>

    <div id="vehicle-status">Status: Idle.</div>
  </div>
  <div id="map"></div>
  <div id="notification"></div>

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
const map = L.map('map').setView([28.4, 83.9], 8);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(map);

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

// Example WebSocket - replace URL as needed
const ws = new WebSocket('ws://103.90.84.153:8081');
ws.onmessage = function (event) {
  const data = JSON.parse(event.data);
  if (!data?.data) return;

  const { imei, lat, lon, speed, datetime } = data.data;
  if (imei !== document.getElementById('imei').value) return;

  if (!vehicleMarker.marker) {
    vehicleMarker.marker = L.marker([lat, lon]).addTo(map);
    map.setView([lat, lon], 15);
  } else {
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
function showNotification(message, duration = 4000) {
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
</script>
</body>
</html>
