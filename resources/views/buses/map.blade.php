@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0">Bus Map & Live Tracking</h1>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-outline-info" id="centerMapBtn">
                        <i class="fas fa-crosshairs"></i> Center Map
                    </button>
                    <button type="button" class="btn btn-outline-success" id="toggleTrafficBtn">
                        <i class="fas fa-road"></i> Toggle Traffic
                    </button>
                </div>
            </div>
            <!-- Map Statistics -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Active Buses</h6>
                                    <h3 id="activeBusCount">0</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-bus fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Delayed</h6>
                                    <h3 id="delayedBusCount">0</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Out of Service</h6>
                                    <h3 id="outOfServiceCount">0</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Routes</h6>
                                    <h3 id="totalRoutes">0</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-route fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Map Container -->
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt"></i> Live Bus Locationsssssssssss
                    </h5>
                    <span class="badge badge-primary" id="lastUpdated">Last updated: Never</span>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
            </div>
            
            <!-- Controls and Legend -->
            <div class="row mt-3">
                <div class="col-md-8">
                    <!-- Bus Legend -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Bus Status Legend</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <span class="badge badge-success">●</span> Active (On Time)
                                </div>
                                <div class="col-md-3">
                                    <span class="badge badge-warning">●</span> Delayed
                                </div>
                                <div class="col-md-3">
                                    <span class="badge badge-danger">●</span> Out of Service
                                </div>
                                <div class="col-md-3">
                                    <span class="badge badge-info">●</span> Idle/Stopped
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Map Controls -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cog"></i> Map Controls</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="autoRefresh">Auto Refresh</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="autoRefresh" checked>
                                    <label class="custom-control-label" for="autoRefresh">Every 30 seconds</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="routeFilter">Filter by Route</label>
                                <select class="form-control" id="routeFilter">
                                    <option value="">All Routes</option>
                                    <option value="kathmandu-pokhara">Kathmandu - Pokhara</option>
                                    <option value="kathmandu-chitwan">Kathmandu - Chitwan</option>
                                    <option value="kathmandu-butwal">Kathmandu - Butwal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>            </div>        </div>
    </div>
</div>
@endsection
 

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
<!-- Custom Map Styles -->
<style>
    .map-controls {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
    }
    .bus-marker {
        font-size: 24px;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    .leaflet-popup-content {
        text-align: center;
    }
    .badge {
        font-size: 12px;
        margin-right: 5px;
    }
    .path-instructions {
        background-color: rgba(255, 255, 255, 0.9);
        border: 1px solid #007bff;
        border-radius: 4px;
        padding: 10px;
        margin: 10px;
        font-size: 14px;
        z-index: 1500;
    }
    .custom-marker {
        display: flex;
        justify-content: center;
        align-items: center;
    }    .custom-marker i {
        font-size: 24px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.4);
        color: #ffffff;
    }
    .custom-marker i.text-success {
        color: #28a745;
        filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.4));
    }
    .custom-marker i.text-danger {
        color: #dc3545;
        filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.4));
    }
    .path-instructions {
        background: none;
        border: none;
    }
    .path-instructions .alert {
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    .leaflet-control-button {
        display: block !important;
    }
    /* .leaflet-routing-container {
        display: block !important;
        opacity: 1 !important;
    }
    .leaflet-routing-alternatives-container {
        display: block !important;
        opacity: 1 !important;
    } */
</style>
@endpush

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.min.js"></script>

<!-- Custom Route Map JS -->
<script src="{{ asset('js/route-map.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the map centered on Kathmandu
    var map = L.map('map').setView([27.7172, 85.3240], 12);
    
    // Add OpenStreetMap tiles
    var osmLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add satellite layer option
    var satelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 19,
        attribution: '© Google Maps'
    });
    
    // Layer control
    var baseMaps = {
        "OpenStreetMap": osmLayer,
        "Satellite": satelliteLayer
    };
    L.control.layers(baseMaps).addTo(map);

    // Path drawing variables
    var drawingPath = false;
    var sourceMarker = null;
    var destinationMarker = null;
    var pathControl = null;
    var userRoutingControl = null;
    var busRoutingControl = null;

    // Add path drawing control button
    var drawButton;
    var pathButton = L.Control.extend({
        options: {
            position: 'topleft'
        },
        onAdd: function(map) {
            var container = L.DomUtil.create('div', 'leaflet-bar leaflet-control');
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

    // Function to start path drawing mode
    function startPathDrawing() {
        drawingPath = true;
        map.getContainer().style.cursor = 'crosshair';
        if (drawButton) {
            drawButton.style.backgroundColor = '#28a745';
            drawButton.querySelector('i').style.color = 'white';
        }
        clearExistingPath();
        
        // Show instructions to user
        const instructions = L.control({position: 'topright'});
        instructions.onAdd = function() {
            const div = L.DomUtil.create('div', 'path-instructions');
            div.innerHTML = '<div class="alert alert-info">' +
                         'Click on the map to set source point, then click again to set destination</div>';
            return div;
        };
        instructions.addTo(map);
        pathControl = instructions;
    }

    // Function to cancel path drawing
    function cancelPathDrawing() {
        drawingPath = false;
        map.getContainer().style.cursor = '';
        if (drawButton) {
            drawButton.style.backgroundColor = 'white';
            drawButton.querySelector('i').style.color = '#666';
        }
        clearExistingPath();
        if (pathControl) {
            map.removeControl(pathControl);
            pathControl = null;
        }
    }

    // Separate routing controls for user path and bus tracking
    var userRoutingControl = null;
    var busRoutingControl = null;

    // Update clearExistingPath to only clear user path
    function clearExistingPath() {
        if (sourceMarker) map.removeLayer(sourceMarker);
        if (destinationMarker) map.removeLayer(destinationMarker);
        if (userRoutingControl) {
            try {
                if (userRoutingControl.getPlan) {
                    userRoutingControl.getPlan().setWaypoints([]);
                }
            } catch (e) {}
            map.removeControl(userRoutingControl);
            userRoutingControl = null;
        }
        sourceMarker = null;
        destinationMarker = null;
    }

    // Update drawRoutePath to use userRoutingControl
    function drawRoutePath(source, destination) {
        if (userRoutingControl) {
            map.removeControl(userRoutingControl);
        }
        userRoutingControl = L.Routing.control({
            waypoints: [
                L.latLng(source[0], source[1]),
                L.latLng(destination[0], destination[1])
            ],
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1',
                profile: 'driving'
            }),
            lineOptions: {
                styles: [{
                    color: '#0075ff',
                    opacity: 0.8,
                    weight: 5
                }]
            },
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            showAlternatives: true,
            altLineOptions: {
                styles: [{
                    color: '#2c3e50',
                    opacity: 0.5,
                    weight: 4
                }]
            },
            createMarker: function() { return null; }
        }).addTo(map);
        userRoutingControl.on('routesfound', function(e) {
            const routes = e.routes;
            const route = routes[0];
            if (pathControl) {
                map.removeControl(pathControl);
            }
            const routeInfo = L.control({position: 'topright'});
            routeInfo.onAdd = function() {
                const div = L.DomUtil.create('div', 'route-info');
                const distance = (route.summary.totalDistance / 1000).toFixed(1);
                const time = Math.round(route.summary.totalTime / 60);
                div.innerHTML = `
                    <div class="alert alert-success" style="padding: 10px; margin: 10px;">
                        <strong>Route Info:</strong><br>
                        Distance: ${distance} km<br>
                        Est. Time: ${time} min
                    </div>`;
                return div;
            };
            routeInfo.addTo(map);
            pathControl = routeInfo;
        });
    }

    // Bus markers storage
    var busMarkers = {};
    var allBuses = [];
    
    // Custom bus icons
    var createBusIcon = function(status) {
        var color;
        switch(status) {
            case 'active':
                color = '#28a745'; // green
                break;
            case 'delayed':
                color = '#ffc107'; // yellow
                break;
            case 'out_of_service':
                color = '#dc3545'; // red
                break;
            case 'idle':
                color = '#17a2b8'; // blue
                break;
            default:
                color = '#6c757d'; // gray
        }
        
        return L.divIcon({
            html: `<i class="fas fa-bus" style="color: ${color}; font-size: 20px; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"></i>`,
            iconSize: [25, 25],
            iconAnchor: [12, 12],
            popupAnchor: [0, -12],
            className: 'bus-marker'
        });
    };
      // Function to add/update bus marker
    function updateBusMarker(bus) {
        var icon = createBusIcon(bus.status);
        var popupContent = `
            <div class="text-center">
                <h6><strong>${bus.bus_name}</strong></h6>
                <p class="mb-1"><strong>Number:</strong> ${bus.bus_number}</p>
                <p class="mb-1"><strong>IMEI:</strong> ${bus.imei || 'Not set'}</p>
                <p class="mb-1"><strong>Status:</strong> 
                    <span class="badge badge-${bus.status === 'active' ? 'success' : bus.status === 'delayed' ? 'warning' : bus.status === 'idle' ? 'info' : 'danger'}">
                        ${bus.status ? bus.status.toUpperCase() : 'UNKNOWN'}
                    </span>
                </p>
                <p class="mb-1"><strong>Speed:</strong> ${bus.speed || 0} km/h</p>
                <p class="mb-1"><small>Last Update: ${bus.last_tracked_at ? new Date(bus.last_tracked_at).toLocaleTimeString() : 'N/A'}</small></p>
                <button class="btn btn-sm btn-primary mt-2" onclick="trackBus(${bus.id})">
                    <i class="fas fa-route"></i> Track Route
                </button>
            </div>
        `;
        
        if (busMarkers[bus.id]) {
            // Update existing marker
            busMarkers[bus.id].setLatLng([bus.latitude, bus.longitude]);
            busMarkers[bus.id].setIcon(icon);
            busMarkers[bus.id].setPopupContent(popupContent);
        } else {
            // Create new marker
            var marker = L.marker([bus.latitude, bus.longitude], {icon: icon})
                .addTo(map)
                .bindPopup(popupContent);
            busMarkers[bus.id] = marker;
        }
    }
    
    // Function to update statistics
    function updateStatistics() {
        var active = allBuses.filter(bus => bus.status === 'active').length;
        var delayed = allBuses.filter(bus => bus.status === 'delayed').length;
        var outOfService = allBuses.filter(bus => bus.status === 'out_of_service').length;
        var routes = [...new Set(allBuses.map(bus => bus.route))].length;
        
        document.getElementById('activeBusCount').textContent = active;
        document.getElementById('delayedBusCount').textContent = delayed;
        document.getElementById('outOfServiceCount').textContent = outOfService;
        document.getElementById('totalRoutes').textContent = routes;
    }
    
    function clearAllMarkers() {
        Object.values(busMarkers).forEach(marker => map.removeLayer(marker));
        busMarkers = {};
    }
    
    // Fetch live buses data from the server
    function fetchLiveBuses() {
        fetch('/api/buses/live')
            .then(res => res.json())
            .then(buses => {
                clearAllMarkers();
                allBuses = buses;
                allBuses.forEach(updateBusMarker);
                updateStatistics();
                document.getElementById('lastUpdated').textContent = 'Last updated: ' + new Date().toLocaleTimeString();
            })
            .catch(() => {
                document.getElementById('lastUpdated').textContent = 'Last updated: Error';
            });
    }
    
    // Button event handlers
    document.getElementById('refreshBtn').addEventListener('click', function() {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        fetchLiveBuses();
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }, 1000);
    });
    
    document.getElementById('centerMapBtn').addEventListener('click', function() {
        map.setView([27.7172, 85.3240], 12);
    });
    
    document.getElementById('toggleTrafficBtn').addEventListener('click', function() {
        // This would toggle traffic layer in a real implementation
        alert('Traffic layer toggle - would be implemented with real traffic data');
    });
    
    // Route filter
    document.getElementById('routeFilter').addEventListener('change', function() {
        var selectedRoute = this.value;
        clearAllMarkers();
        var filteredBuses = selectedRoute ? 
            allBuses.filter(bus => (bus.route || '').toLowerCase().includes(selectedRoute)) : 
            allBuses;
            
        filteredBuses.forEach(updateBusMarker);
    });
    
    // Auto refresh toggle
    var autoRefreshInterval;
    document.getElementById('autoRefresh').addEventListener('change', function() {
        if (this.checked) {
            autoRefreshInterval = setInterval(fetchLiveBuses, 30000);
        } else {
            clearInterval(autoRefreshInterval);
        }
    });
      // Function to draw route path
    function drawRoutePath(source, destination) {
        if (userRoutingControl) {
            map.removeControl(userRoutingControl);
        }
        userRoutingControl = L.Routing.control({
            waypoints: [
                L.latLng(source[0], source[1]),
                L.latLng(destination[0], destination[1])
            ],
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1',
                profile: 'driving'
            }),
            lineOptions: {
                styles: [{
                    color: '#0075ff',
                    opacity: 0.8,
                    weight: 5
                }]
            },
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: true,
            showAlternatives: true,
            altLineOptions: {
                styles: [{
                    color: '#2c3e50',
                    opacity: 0.5,
                    weight: 4
                }]
            },
            createMarker: function() { return null; }
        }).addTo(map);

        // Show route info in a custom control
        userRoutingControl.on('routesfound', function(e) {
            const routes = e.routes;
            const route = routes[0]; // Get primary route

            if (pathControl) {
                map.removeControl(pathControl);
            }

            // Create route info control
            const routeInfo = L.control({position: 'topright'});
            routeInfo.onAdd = function() {
                const div = L.DomUtil.create('div', 'route-info');
                const distance = (route.summary.totalDistance / 1000).toFixed(1);
                const time = Math.round(route.summary.totalTime / 60);
                
                div.innerHTML = `
                    <div class="alert alert-success" style="padding: 10px; margin: 10px;">
                        <strong>Route Info:</strong><br>
                        Distance: ${distance} km<br>
                        Est. Time: ${time} min
                    </div>`;
                return div;
            };
            routeInfo.addTo(map);
            pathControl = routeInfo;
        });
    }

    // Update trackBus to use busRoutingControl
    window.trackBus = function(busId) {
        var bus = allBuses.find(b => b.id === busId);
        if (bus) {
            map.setView([bus.latitude, bus.longitude], 16);
            busMarkers[bus.id].openPopup();
            // Remove previous bus route only
            if (busRoutingControl) {
                try {
                    if (busRoutingControl.getPlan) {
                        busRoutingControl.getPlan().setWaypoints([]);
                    }
                } catch (e) {}
                map.removeControl(busRoutingControl);
                busRoutingControl = null;
            }
            // Fetch and show custom path if it exists
            fetch(`/buses/${busId}/custom-path`)
                .then(response => response.json())
                .then(data => {
                    if (data.source && data.destination) {
                        busRoutingControl = L.Routing.control({
                            waypoints: [
                                L.latLng(data.source[0], data.source[1]),
                                L.latLng(data.destination[0], data.destination[1])
                            ],
                            router: L.Routing.osrmv1({
                                serviceUrl: 'https://router.project-osrm.org/route/v1',
                                profile: 'driving'
                            }),
                            lineOptions: {
                                styles: [{
                                    color: '#ff9900',
                                    opacity: 0.8,
                                    weight: 5
                                }]
                            },
                            addWaypoints: false,
                            draggableWaypoints: false,
                            fitSelectedRoutes: true,
                            // showAlternatives: false,
                            createMarker: function() { return null; }
                        }).addTo(map);
                    }
                })
                .catch(error => console.error('Error fetching custom path:', error));
        }
    };
    
    // Initial load
    fetchLiveBuses();
    
    // Start auto refresh
    autoRefreshInterval = setInterval(fetchLiveBuses, 30000);
});
</script>
@endpush