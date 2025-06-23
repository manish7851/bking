@extends('layouts.sidebar')

 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Bus {{ $bus->bus_number }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />

    <style>
        #map {
            height: 600px;
            width: 100%;
            border-radius: 8px;
        }
        .info-card {
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .history-line {
            stroke: rgba(0, 0, 255, 0.5);
            stroke-width: 3;
        }
        .bus-icon-container img {
            width: 32px;
            height: 32px;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-2 mt-4">
            <h1>Bus Tracking - {{ $bus->bus_name }}</h1>

            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('buses.index') }}" class="btn btn-secondary">
                    ← Back to Buses
                </a>
            </div>

            <div class="row">
                <!-- Bus Info -->
                <div class="col-md-3">
                    <div class="card info-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Bus Information</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Bus Name:</strong> {{ $bus->bus_name }}</p>
                            <p><strong>Bus Number:</strong> {{ $bus->bus_number }}</p>
                            <p><strong>Status:</strong> 
                                <span class="badge {{ $bus->tracking_enabled ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $bus->tracking_enabled ? 'Tracking Active' : 'Tracking Disabled' }}
                                </span>
                            </p>
                            <p><strong>Last Update:</strong> 
                                <span id="last-update">
                                    {{ $bus->last_tracked_at ? $bus->last_tracked_at->diffForHumans() : 'Never' }}
                                </span>
                            </p>
                            <p><strong>Speed:</strong> <span id="current-speed">{{ $bus->speed ?? 0 }}</span> km/h</p>
                            <p><strong>Heading:</strong> <span id="current-heading">{{ $bus->heading ?? 0 }}</span>°</p>
                        </div>
                    </div>

                    <div class="card info-card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Tracking Options</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('buses.tracking', $bus->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn {{ $bus->tracking_enabled ? 'btn-warning' : 'btn-success' }} w-100">
                                    {{ $bus->tracking_enabled ? 'Stop Tracking' : 'Start Tracking' }}
                                </button>
                            </form>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="show-path" checked>
                                <label class="form-check-label" for="show-path">
                                    Show Travel Path
                                </label>
                            </div>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" id="auto-refresh" checked>
                                <label class="form-check-label" for="auto-refresh">
                                    Auto Refresh (10s)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Display -->
                <div class="col-md-9">
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@php
     $activeRoute = $bus->routes()
                                        ->whereDate('trip_date', '>=', now()->format('Y-m-d'))
                                        ->orderBy('trip_date')
                                        ->first();
    @endphp
<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script src="{{ asset('js/route-map.js') }}"></script>

<script>
    var busInfo = <?=json_encode($bus) ?>;
    console.log(busInfo);
    var activeRoute = <?=json_encode($activeRoute) ?>;
    console.log(activeRoute);
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        const map = L.map('map').setView([27.7172, 85.3240], 12); // Default to Kathmandu
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Bus marker and path variables
        let busMarker;
        let pathLine;
        let busIcon = L.divIcon({
            className: 'bus-icon-container',
            html: '<img src="/images/bus-icon.png" alt="Bus" style="transform: rotate(0deg);" />',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        // If bus icon doesn't exist, create a default one
        if (!busIcon) {
            busIcon = L.divIcon({
                className: 'bus-icon-container',
                html: '<div style="background-color: blue; border-radius: 50%; width: 24px; height: 24px;"></div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
        }
        
        // Planned route variable
        let plannedRouteControl = null;
        // Function to fetch and display the planned route
        function fetchAndDrawPlannedRoute() {
            // Remove previous route if any
            if (plannedRouteControl) {
                map.removeControl(plannedRouteControl);
                plannedRouteControl = null;
            }
            fetch('/buses/{{ $bus->id }}/route-coordinates')
                .then(response => response.json())
                .then((data) => {
                    if (data.source && data.destination) {
                        // plannedRouteControl = L.Routing.control({
                        //     waypoints: [
                        //         L.latLng(data.source[0], data.source[1]),
                        //         L.latLng(data.destination[0], data.destination[1])
                        //     ],
                        //     router: L.Routing.osrmv1({
                        //         serviceUrl: 'https://router.project-osrm.org/route/v1',
                        //         profile: 'driving'
                        //     }),
                        //     lineOptions: {
                        //         styles: [{ color: '#ff9900', opacity: 0.8, weight: 5 }]
                        //     },
                        //     addWaypoints: false,
                        //     draggableWaypoints: false,
                        //     fitSelectedRoutes: true,
                        //     showAlternatives: false,
                        //     createMarker: function() { return null; }
                        // }).addTo(map);
                    }
                })
                .catch(error => console.error('Error fetching planned route:', error));
        }
        
        // Function to fetch bus location data
        function fetchBusData() {
            fetch(`{{ route('buses.locations', $bus->id) }}`)
                .then(response => response.json())
                .then(data => {
                    updateBusMarker(data.bus);
                    if (document.getElementById('show-path').checked) {
                        updatePathLine(data.locations);
                    } else if (pathLine) {
                        map.removeLayer(pathLine);
                        pathLine = null;
                    }
                })
                .catch(error => console.error('Error fetching bus data:', error));
        }
        
        // Function to update the bus marker on the map
        function updateBusMarker(busData) {
            // Update bus info panel
            document.getElementById('current-speed').textContent = busData.speed ? busData.speed + ' km/h' : '0 km/h';
            document.getElementById('current-heading').textContent = busData.heading ? busData.heading + '°' : '0°';
            document.getElementById('last-update').textContent = busData.last_tracked_at ? 
                new Date(busData.last_tracked_at).toLocaleString() : 'Never';
            
            // If bus has location data, update or create marker
            if (busData.latitude && busData.longitude) {
                const position = [busData.latitude, busData.longitude];
                
                // Update icon rotation based on heading
                if (busData.heading) {
                    const icon = L.divIcon({
                        className: 'bus-icon-container',
                        html: `<img src="/images/bus-icon.png" alt="Bus" style="transform: rotate(${busData.heading}deg);" />`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16]
                    });
                    
                    if (busMarker) {
                        busMarker.setIcon(icon);
                        busMarker.setLatLng(position);
                    } else {
                        busMarker = L.marker(position, {icon: icon}).addTo(map);
                    }
                } else {
                    if (busMarker) {
                        busMarker.setLatLng(position);
                    } else {
                        busMarker = L.marker(position, {icon: busIcon}).addTo(map);
                    }
                }
                
                busMarker.bindPopup(`
                    <b>${busData.bus_name}</b><br>
                    Bus #: ${busData.bus_number}<br>
                    Speed: ${busData.speed || 0} km/h<br>
                    Last update: ${new Date(busData.last_tracked_at || Date.now()).toLocaleString()}
                `);
                
                // Center map on bus position
                map.setView(position, map.getZoom());
            }
        }
        
        // Function to draw the path line from location history
        function updatePathLine(locations) {
            if (locations && locations.length > 0) {
                const points = locations.map(loc => [loc.latitude, loc.longitude]);
                
                if (pathLine) {
                    map.removeLayer(pathLine);
                }
                
                pathLine = L.polyline(points, {
                    color: 'blue',
                    weight: 3,
                    opacity: 0.7,
                    className: 'history-line'
                }).addTo(map);
                
                // Fit map bounds to include the full path
                map.fitBounds(pathLine.getBounds(), { padding: [50, 50] });
            }
        }
        
        // Draw Path feature variables
        let drawingPath = false;
        let sourceMarker = null;
        let destinationMarker = null;
        let userRoutingControl = null;
        let drawButton;
        let pathControl = null;

        // Add Draw Path button
        const pathButton = L.Control.extend({
            options: { position: 'topleft' },
            onAdd: function(map) {
                const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
                drawButton = L.DomUtil.create('a', 'leaflet-control-button', container);
                drawButton.innerHTML = '<i class="fas fa-route" style="line-height: 30px; color: #666;"></i>';
                drawButton.title = 'Draw Path';
                drawButton.style.width = '30px';
                drawButton.style.height = '30px';
                drawButton.style.textAlign = 'center';
                drawButton.style.cursor = 'pointer';
                drawButton.style.backgroundColor = 'white';
                drawButton.onclick = function() {
                    if (!drawingPath) {
                        startPathDrawing();
                    } else {
                        cancelPathDrawing();
                    }
                    return false;
                };
                return container;
            }
        });
        map.addControl(new pathButton());

        // Start drawing mode
        function startPathDrawing() {
            drawingPath = true;
            map.getContainer().style.cursor = 'crosshair';
            if (drawButton) {
                drawButton.style.backgroundColor = '#28a745';
                drawButton.querySelector('i').style.color = 'white';
            }
            clearUserPath();
            // Show instructions
            const instructions = L.control({position: 'topright'});
            instructions.onAdd = function() {
                const div = L.DomUtil.create('div', 'path-instructions');
                div.innerHTML = '<div class="alert alert-info">Click on the map to set source point, then click again to set destination</div>';
                return div;
            };
            instructions.addTo(map);
            pathControl = instructions;
            let clickCount = 0;
            let tempSource = null;
            let tempDest = null;
            function onMapClick(e) {
                if (clickCount === 0) {
                    tempSource = e.latlng;
                    if (sourceMarker) map.removeLayer(sourceMarker);
                    sourceMarker = L.marker(tempSource, {draggable: false}).addTo(map).bindPopup('Source').openPopup();
                    clickCount++;
                } else if (clickCount === 1) {
                    tempDest = e.latlng;
                    if (destinationMarker) map.removeLayer(destinationMarker);
                    destinationMarker = L.marker(tempDest, {draggable: false}).addTo(map).bindPopup('Destination').openPopup();
                    drawUserRoute(tempSource, tempDest);
                    map.off('click', onMapClick);
                    drawingPath = false;
                    map.getContainer().style.cursor = '';
                    if (drawButton) {
                        drawButton.style.backgroundColor = 'white';
                        drawButton.querySelector('i').style.color = '#666';
                    }
                    if (pathControl) {
                        map.removeControl(pathControl);
                        pathControl = null;
                    }
                }
            }
            map.on('click', onMapClick);
        }

        function cancelPathDrawing() {
            drawingPath = false;
            map.getContainer().style.cursor = '';
            if (drawButton) {
                drawButton.style.backgroundColor = 'white';
                drawButton.querySelector('i').style.color = '#666';
            }
            clearUserPath();
            if (pathControl) {
                map.removeControl(pathControl);
                pathControl = null;
            }
        }

        function clearUserPath() {
            if (sourceMarker) { map.removeLayer(sourceMarker); sourceMarker = null; }
            if (destinationMarker) { map.removeLayer(destinationMarker); destinationMarker = null; }
            if (userRoutingControl) {
                try { if (userRoutingControl.getPlan) userRoutingControl.getPlan().setWaypoints([]); } catch (e) {}
                map.removeControl(userRoutingControl);
                userRoutingControl = null;
            }
        }

        function drawUserRoute(source, dest) {
            if (userRoutingControl) map.removeControl(userRoutingControl);
            userRoutingControl = L.Routing.control({
                waypoints: [L.latLng(source.lat, source.lng), L.latLng(dest.lat, dest.lng)],
                router: L.Routing.osrmv1({
                    serviceUrl: 'https://router.project-osrm.org/route/v1',
                    profile: 'driving'
                }),
                lineOptions: {
                    styles: [{ color: '#0075ff', opacity: 0.8, weight: 5 }]
                },
                addWaypoints: false,
                draggableWaypoints: false,
                fitSelectedRoutes: true,
                showAlternatives: false,
                createMarker: function() { return null; }
            }).addTo(map);
            // Show Save Path button
            showSavePathButton(source, dest);
        }

        // Show Save Path button and handle save
        function showSavePathButton(source, dest) {
            let saveBtn = document.getElementById('save-path-btn');
            if (!saveBtn) {
                saveBtn = document.createElement('button');
                saveBtn.id = 'save-path-btn';
                saveBtn.className = 'btn btn-success position-absolute';
                saveBtn.style.top = '80px';
                saveBtn.style.left = '50%';
                saveBtn.style.transform = 'translateX(-50%)';
                saveBtn.innerText = 'Save Path';
                document.body.appendChild(saveBtn);
            }
            saveBtn.style.display = 'block';
            saveBtn.onclick = function() {
                fetch('/buses/{{ $bus->id }}/custom-path', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        source: [source.lat, source.lng],
                        destination: [dest.lat, dest.lng]
                    })
                })
                .then(res => res.json())
                .then(data => {
                    saveBtn.innerText = 'Saved!';
                    setTimeout(() => { saveBtn.style.display = 'none'; saveBtn.innerText = 'Save Path'; }, 1200);
                    fetchAndDrawPlannedRoute();
                })
                .catch(() => { saveBtn.innerText = 'Error!'; });
            };
        }

        // Load and show custom path if it exists
        function loadCustomPath() {
            fetch('/buses/{{ $bus->id }}/custom-path')
                .then(res => res.json())
                .then(data => {
                    if (data.source && data.destination) {
                        drawUserRoute({lat: data.source[0], lng: data.source[1]}, {lat: data.destination[0], lng: data.destination[1]});
                    }
                });
        }

        // Initial data fetch
        fetchBusData();
        fetchAndDrawPlannedRoute();
        loadCustomPath();
        
        // Auto-refresh toggle
        let refreshInterval;
        
        function setAutoRefresh() {
            if (document.getElementById('auto-refresh').checked) {
                refreshInterval = setInterval(fetchBusData, 10000); // Refresh every 10 seconds
            } else if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Setup event listeners
        document.getElementById('auto-refresh').addEventListener('change', setAutoRefresh);
        document.getElementById('show-path').addEventListener('change', fetchBusData);
        
        // Initial setup of auto-refresh
        setAutoRefresh();
    });
</script>



<script>
     const map = L.map('map').setView([27.9634, 84.6548], 7); // Midpoint between Kathmandu and Pokhara

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);
  // Add the routing control
  L.Routing.control({
    waypoints: [
    //   L.latLng(29.046484, 83.485705), // Kathmandu
    //   L.latLng(27.734943, 85.311397),  // Pokhara
      L.latLng(Number(activeRoute.source_latitude), Number(activeRoute.source_longitude)),
      L.latLng(Number(activeRoute.destination_latitude), Number(activeRoute.destination_longitude)) // Midpoint
    ],
    routeWhileDragging: false,
    lineOptions: {
      styles: [{ color: 'blue', opacity: 0.7, weight: 5 }]
    },
    showAlternatives: true,
    show: false,
    createMarker: function(i, waypoint, n) {
      const labels = [activeRoute.source, activeRoute.destination];
      return L.marker(waypoint.latLng).bindPopup(labels[i]).openPopup();
    }
  }).addTo(map);

// // Center map on bus position
// map.setView(position, map.getZoom());


// Replace with your WebSocket server URL
const socket = new WebSocket('ws://103.90.84.153:8081');

const output = document.getElementById('output');

socket.addEventListener('open', () => {
    console.log('Connected to WebSocket server');
    // output.textContent = 'Connected. Waiting for messages...\n';
});

socket.addEventListener('message', (event) => {
    console.log('Received:', event.data);

    try {
        const message = JSON.parse(event.data);

        // Check message type and structure
        if ((message.type='update' || message.type === 'initial_state') && message.data && message.data[0].imei) {
            const {
                imei, lat, lon, speed, course, datetime, lastUpdate
            } = message.data[0];

            const formatted = `
              IMEI: ${imei}
              Latitude: ${lat}
              Longitude: ${lon}
              Speed: ${speed} km/h
              Heading: ${course}
              Datetime: ${datetime}
              Last Update: ${lastUpdate}
            `;
            const busData = {heading: 24, bus_number: busInfo.bus_number, bus_name: busInfo.bus_name, speed: speed, last_tracked_at: lastUpdate}
            // const position = [busData.latitude, busData.longitude];
            const position = [lat, lon];
            // const position = [27.7172, 85.3240];

            let busMarker;                
            // Update icon rotation based on heading
            if (/**busData.heading**/ busInfo.tracking_enabled) {
                const icon = L.divIcon({
                    className: 'bus-icon-container',
                    html: `<img src="/bus.svg" alt="Bus" style="transform: rotate(${course}deg);" />`,
                    iconSize: [32, 32],
                    iconAnchor: [16, 16]
                });
                
                if (busMarker) {
                    busMarker.setIcon(icon);
                    busMarker.setLatLng(position);
                } else {
                    busMarker = L.marker(position, {icon: icon}).addTo(map);
                }
                // Send updated location to server via AJAX to update in DB
                fetch(`/buses/${busInfo.id}/update-location`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                        // 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        api_key: 'public_api_key_for_location_updates',
                        imei: imei,
                        bus_id: busInfo.id,
                        latitude: lat,
                        longitude: lon,
                        speed: speed,
                        heading: course,
                        last_tracked_at: lastUpdate
                    })
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Location updated via updateLocationFromGPS:', data);
                })
                .catch(error => {
                    console.error('Error updating location:', error);
                });
            } else {
                if (busMarker) {
                    busMarker.setLatLng(position);
                } else {
                    busMarker = L.marker(position, {icon: busIcon}).addTo(map);
                }
            }
            map.setView(position, map.getZoom());
            busMarker.bindPopup(`
                <b>${busData.bus_name}</b><br>
                Bus #: ${busData.bus_number}<br>
                Speed: ${busData.speed || 0} km/h<br>
                Last update: ${new Date(busData.last_tracked_at || Date.now()).toLocaleString()}
            `);

            console.log(formatted);
            // output.textContent = formatted;
        } else {
            console.warn('Unknown message format:', message);
        }
    } catch (err) {
        console.error('Error parsing message:', err);
        // output.textContent += `\nError parsing message: ${err.message}`;
    }
});

socket.addEventListener('close', () => {
    console.log('WebSocket connection closed');
    // output.textContent += '\nDisconnected from server.';
});

socket.addEventListener('error', (err) => {
    console.error('WebSocket error:', err);
    // output.textContent += `\nWebSocket error: ${err.message}`;
});
// const busData = {heading: 24, bus_number: "2345", bus_name: "ldjf", speed: 40}
// // const position = [busData.latitude, busData.longitude];
// const position = [27.7172, 85.3240];
// let busMarker;                
// // Update icon rotation based on heading
// if (/**busData.heading**/ true) {
//     const icon = L.divIcon({
//         className: 'bus-icon-container',
//         html: `<img src="/bus.svg" alt="Bus" style="transform: rotate(${busData.heading}deg);" />`,
//         iconSize: [32, 32],
//         iconAnchor: [16, 16]
//     });
    
//     if (busMarker) {
//         busMarker.setIcon(icon);
//         busMarker.setLatLng(position);
//     } else {
//         busMarker = L.marker(position, {icon: icon}).addTo(map);
//     }
// } else {
//     busMarker = L.marker(position, {icon: busIcon}).addTo(map);
// }

// busMarker.bindPopup(`
//     <b>${busData.bus_name}</b><br>
//     Bus #: ${busData.bus_number}<br>
//     Speed: ${busData.speed || 0} km/h<br>
//     Last update: ${new Date(busData.last_tracked_at || Date.now()).toLocaleString()}
// `);
</script>
</body>
</html>

