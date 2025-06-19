@extends('layouts.dashboard')

    
    @php
    $activeLink = 'bus-map';
    @endphp

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Bus GPS Tracking Dashboard</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <!-- MarkerCluster CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
        
        <style>
            #map-container {
                position: relative;
                height: calc(50vh - 100px);
            }
            #map {
                height: 300px;
                width: auto;
                z-index: 1;
            }
            .dashboard-card {
                transition: all 0.3s;
                border-radius: 10px;
                overflow: hidden;
            }
            .dashboard-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            }
            .dashboard-number {
                font-size: 2rem;
                font-weight: bold;
            }
            .stats-overlay {
                position: absolute;
                top: -200px;
                left: 850px;
                z-index: 999;
                background-color: rgba(255,255,255,0.9);
                border-radius: 8px;
                padding: 15px;
                width: 300px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            .alerts-overlay {
                position: absolute;
                left: 850px;
                top:100px;
                z-index: 999;
                background-color: rgba(255,255,255,0.9);
                border-radius: 8px;
                padding: 1px;
                width: 300px;
              
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            }
            .bus-marker-icon {
                width: 30px;
                height: 30px;
                border-radius: 50%;
                text-align: center;
                line-height: 30px;
                font-weight: bold;
                color: white;
            }
            .alert-item {
                padding: 10px;
                margin-bottom: 10px;
                border-radius: 5px;
                border-left: 5px solid;
            }
            .alert-item.warning { border-left-color: #ffc107; background-color: #fff8e1; }
            .alert-item.danger { border-left-color: #dc3545; background-color: #f8d7da; }
            .alert-item.info { border-left-color: #0dcaf0; background-color: #e7f9fd; }
        </style>
    </head>
    <body>

    <div class="container-fluid" style="margin-left: 200px;">
        <div class="row">
            <div class="col-md-10 offset-md-2 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1>GPS Tracking Dashboard</h1>
                    <div>
                        <a href="{{ route('buses.index') }}" class="btn btn-outline-primary me-2">Bus Management</a>
                        <button id="refresh-data" class="btn btn-primary">Refresh Data</button>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Buses</h5>
                                <p class="dashboard-number" id="active-buses-count">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Moving Buses</h5>
                                <p class="dashboard-number" id="moving-buses-count">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-warning text-dark">
                            <div class="card-body">
                                <h5 class="card-title">Idle Buses</h5>
                                <p class="dashboard-number" id="idle-buses-count">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Routes</h5>
                                <p class="dashboard-number">{{ $routeCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Map Container -->
                <div id="map-container">
                    <div id="map"></div>
                    
                    <!-- Stats Overlay -->
                    <div class="stats-overlay">
                        <h5>Live Statistics</h5>
                        <div class="mt-3">
                            <p><strong>Last updated:</strong> <span id="last-update-time">-</span></p>
                            <p><strong>Average speed:</strong> <span id="avg-speed">0</span> km/h</p>
                            <p><strong>Buses in motion:</strong> <span id="moving-percentage">0</span>%</p>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto-refresh" checked>
                            <label class="form-check-label" for="auto-refresh">Auto refresh (15s)</label>
                        </div>
                    </div>
                    
                    <!-- Alerts Overlay -->
                    <div class="alerts-overlay">
                        <h5>Recent Alerts</h5>
                        <div id="alerts-container">
                            <div class="alert-item info">
                                <strong>Loading alerts...</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <!-- Optional: Echo for real-time updates -->
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.0.1/dist/web/pusher.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize map
            const map = L.map('map').setView([27.7172, 85.3240], 12); // Default to Kathmandu
            
            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            // Initialize a marker cluster group
            const markers = L.markerClusterGroup();
            map.addLayer(markers);
            
            // Store bus markers
            const busMarkers = {};
            
            // Function to fetch all bus data
            function fetchBusData() {
                fetch('{{ route("buses.track.all.data") }}')
                    .then(response => response.json())
                    .then(buses => {
                        updateMarkers(buses);
                        updateStatistics(buses);
                        document.getElementById('last-update-time').textContent = moment().format('HH:mm:ss');
                    })
                    .catch(error => console.error('Error fetching bus data:', error));
            }
            
            // Function to fetch and display recent alerts
            function fetchAlerts() {
                fetch('/api/alerts?limit=5&api_key={{ config("services.tracking.api_key") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.alerts) {
                            updateAlertsPanel(data.alerts);
                        }
                    })
                    .catch(error => console.error('Error fetching alerts:', error));
            }
            
            // Function to update bus markers on the map
            function updateMarkers(buses) {
                // Clear existing markers
                markers.clearLayers();
                
                const bounds = L.latLngBounds();
                let activeBuses = 0;
                let movingBuses = 0;
                let idleBuses = 0;
                
                buses.forEach(bus => {
                    if (!bus.latitude || !bus.longitude) return;
                    
                    activeBuses++;
                    if (bus.speed > 5) movingBuses++;
                    else if (bus.speed <= 5 && bus.speed > 0) idleBuses++;
                    
                    const position = [bus.latitude, bus.longitude];
                    bounds.extend(position);
                    
                    // Create custom marker
                    const markerColor = bus.speed > 0 ? '#28a745' : '#6c757d';
                    const icon = L.divIcon({
                        className: 'bus-marker-icon',
                        html: `<div style="background-color: ${markerColor}; width: 30px; height: 30px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; color: white; border: 2px solid white;">${bus.id}</div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });
                    
                    const marker = L.marker(position, { icon: icon });
                    
                    marker.bindPopup(`
                        <div style="min-width: 200px;">
                            <h5>${bus.bus_name}</h5>
                            <p><strong>Bus #:</strong> ${bus.bus_number}</p>
                            <p><strong>Status:</strong> <span class="badge ${bus.speed > 0 ? 'bg-success' : 'bg-secondary'}">${bus.speed > 0 ? 'Moving' : 'Stopped'}</span></p>
                            <p><strong>Speed:</strong> ${bus.speed || 0} km/h</p>
                            <p><strong>Last Update:</strong> ${moment(bus.last_tracked_at).fromNow()}</p>
                            <a href="/buses/${bus.id}/track" class="btn btn-sm btn-primary">View Details</a>
                        </div>
                    `);
                    
                    markers.addLayer(marker);
                    busMarkers[bus.id] = marker;
                });
                
                // Update statistics
                document.getElementById('active-buses-count').textContent = activeBuses;
                document.getElementById('moving-buses-count').textContent = movingBuses;
                document.getElementById('idle-buses-count').textContent = idleBuses;
                
                // Fit map to bounds if there are buses
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [50, 50] });
                }
                addMapButtonEvents(); // <-- Add this line
            }
            
            // Function to update statistics based on bus data
            function updateStatistics(buses) {
                if (buses.length === 0) {
                    document.getElementById('avg-speed').textContent = '0';
                    document.getElementById('moving-percentage').textContent = '0';
                    return;
                }
                
                const speedSum = buses.reduce((sum, bus) => sum + (bus.speed || 0), 0);
                const avgSpeed = (speedSum / buses.length).toFixed(1);
                
                const movingBuses = buses.filter(bus => bus.speed > 0).length;
                const movingPercentage = buses.length > 0 ? 
                    Math.round((movingBuses / buses.length) * 100) : 0;
                
                document.getElementById('avg-speed').textContent = avgSpeed;
                document.getElementById('moving-percentage').textContent = movingPercentage;
            }
            
            // Function to update alerts panel
            function updateAlertsPanel(alerts) {
                const container = document.getElementById('alerts-container');
                container.innerHTML = '';
                
                if (alerts.length === 0) {
                    container.innerHTML = '<div class="alert-item info"><p>No recent alerts</p></div>';
                    return;
                }
                
                alerts.forEach(alert => {
                    const alertType = getSeverityClass(alert.severity);
                    const alertHtml = `
                        <div class="alert-item ${alertType}">
                            <p class="mb-1"><strong>${moment(alert.created_at).format('HH:mm:ss')}</strong> - ${alert.message}</p>
                            <small>${moment(alert.created_at).fromNow()}</small>
                        </div>
                    `;
                    container.innerHTML += alertHtml;
                });
            }
            
            // Helper function to get alert class based on severity
            function getSeverityClass(severity) {
                switch (severity) {
                    case 'critical': return 'danger';
                    case 'warning': return 'warning';
                    default: return 'info';
                }
            }
            
            // Function to draw route path for a bus (source to destination)
            function drawBusRoute(busId) {
                fetch(`/buses/${busId}/route-coordinates`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.source && data.destination) {
                            L.Routing.control({
                                waypoints: [
                                    L.latLng(data.source[0], data.source[1]),
                                    L.latLng(data.destination[0], data.destination[1])
                                ],
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
                        }
                    })
                    .catch(error => console.error('Error fetching route:', error));
            }

            // Add map button click event to each bus marker popup
            function addMapButtonEvents() {
                Object.keys(busMarkers).forEach(function(busId) {
                    const marker = busMarkers[busId];
                    marker.on('popupopen', function() {
                        const popup = marker.getPopup();
                        if (popup) {
                            // Add event listener to the map button
                            setTimeout(function() {
                                const btn = document.querySelector(`a[href='/buses/${busId}/track']`);
                                if (btn) {
                                    btn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        drawBusRoute(busId);
                                    });
                                }
                            }, 100); // Wait for popup DOM
                        }
                    });
                });
            }

            // Set up real-time updates with Echo if available
            try {
                window.Echo = new Echo({
                    broadcaster: 'pusher',
                    key: '{{ env("PUSHER_APP_KEY") }}',
                    cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
                    forceTLS: true
                });
                
                window.Echo.channel('bus-tracking')
                    .listen('.bus.location.updated', (e) => {
                        // Update specific bus marker if it exists
                        if (busMarkers[e.id]) {
                            const position = [e.latitude, e.longitude];
                            busMarkers[e.id].setLatLng(position);
                            
                            // Update popup content
                            const popup = busMarkers[e.id].getPopup();
                            if (popup) {
                                popup.setContent(`
                                    <div style="min-width: 200px;">
                                        <h5>${e.bus_name}</h5>
                                        <p><strong>Bus #:</strong> ${e.bus_number}</p>
                                        <p><strong>Status:</strong> <span class="badge ${e.speed > 0 ? 'bg-success' : 'bg-secondary'}">${e.speed > 0 ? 'Moving' : 'Stopped'}</span></p>
                                        <p><strong>Speed:</strong> ${e.speed || 0} km/h</p>
                                        <p><strong>Last Update:</strong> Just now</p>
                                        <a href="/buses/${e.id}/track" class="btn btn-sm btn-primary">View Details</a>
                                    </div>
                                `);
                            }
                        }
                    });
                    
                console.log('Real-time updates enabled');
            } catch (e) {
                console.log('Real-time updates not available:', e);
            }
            
            // Initial data fetch
            fetchBusData();
            fetchAlerts();
            
            // Set up auto-refresh
            let refreshInterval;
            
            function setAutoRefresh() {
                if (document.getElementById('auto-refresh').checked) {
                    refreshInterval = setInterval(() => {
                        fetchBusData();
                        fetchAlerts();
                    }, 15000); // Refresh every 15 seconds
                } else if (refreshInterval) {
                    clearInterval(refreshInterval);
                }
            }
            
            // Setup event listeners
            document.getElementById('refresh-data').addEventListener('click', () => {
                fetchBusData();
                fetchAlerts();
            });
            
            document.getElementById('auto-refresh').addEventListener('change', setAutoRefresh);
            
            // Initial setup of auto-refresh
            setAutoRefresh();
        });
    </script>
    </body>
    </html>

