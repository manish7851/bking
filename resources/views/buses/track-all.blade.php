@extends('layouts.sidebar')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track All Buses</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    
    <style>
        #map {
            height: 700px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .bus-table {
            max-height: 700px;
            overflow-y: auto;
        }
        .highlight-row {
            background-color: #e6f7ff;
        }
        .bus-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-2 mt-4">
            <h1>Live Bus Tracking</h1>

            <!-- Back Button -->
            <div class="mb-4">
                <a href="{{ route('buses.index') }}" class="btn btn-secondary">
                    ← Back to Busesss
                </a>
            </div>

            <div class="row">
                <!-- Map Display -->
                <div class="col-md-8">
                    <div id="map"></div>
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">Tracking Options</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch d-inline-block me-4">
                                <input class="form-check-input" type="checkbox" id="auto-center" checked>
                                <label class="form-check-label" for="auto-center">
                                    Auto-center Map
                                </label>
                            </div>
                            <div class="form-check form-switch d-inline-block me-4">
                                <input class="form-check-input" type="checkbox" id="show-labels" checked>
                                <label class="form-check-label" for="show-labels">
                                    Show Bus Labels
                                </label>
                            </div>
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input" type="checkbox" id="auto-refresh" checked>
                                <label class="form-check-label" for="auto-refresh">
                                    Auto Refresh (10s)
                                </label>
                            </div>
                            <div class="mt-2">
                                <button id="refresh-now" class="btn btn-primary">
                                    Refresh Now
                                </button>
                                <span id="last-update-time" class="text-muted ms-3">
                                    Last update: Never
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bus List -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Active Buses</h5>
                                <span class="badge bg-light text-dark" id="active-bus-count">{{ count($buses) }}</span>
                            </div>
                        </div>
                        <div class="card-body p-0 bus-table">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bus</th>
                                        <th>Status</th>
                                        <th>Speed</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="bus-list">
                                    @foreach($buses as $bus)
                                    <tr data-bus-id="{{ $bus->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
<div class="bus-icon me-2" style="background-color: {!! '#'.substr(md5($bus->id), 0, 6) !!}"></div>
<div></div>
                                                <div>
                                                    <strong>{{ $bus->bus_number }}</strong><br>
                                                    <small>{{ $bus->bus_name }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $bus->speed > 0 ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $bus->speed > 0 ? 'Moving' : 'Stopped' }}
                                            </span>
                                        </td>
                                        <td>{{ $bus->speed ?? 0 }} km/h</td>
                                        <td>
                                            <a href="{{ route('buses.track', $bus->id) }}" class="btn btn-sm btn-info">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                    @if(count($buses) == 0)
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            No buses are currently being tracked.
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
<script src="https://cdn.jsdelivr.net/npm/pusher-js@8.0.1/dist/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map
        const map = L.map('map').setView([27.7172, 85.3240], 12); // Default to Kathmandu
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Store bus markers
        const busMarkers = {};
        
        // Function to fetch all active buses
        function fetchAllBuses() {
            fetch("{{ route('buses.track.all.data') }}")
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(buses => {
                    updateBusMarkers(buses);
                    updateBusList(buses);
                    document.getElementById('last-update-time').textContent = 'Last update: ' + new Date().toLocaleTimeString();
                })
                .catch(error => {
                    console.error('Error fetching bus data:', error);
                    alert('Failed to fetch bus data. Please try again later.');
                });
        }
        
        // Function to update bus markers on the map
        function updateBusMarkers(buses) {
            const bounds = L.latLngBounds();
            const showLabels = document.getElementById('show-labels').checked;
            
            buses.forEach(bus => {
                if (bus.latitude && bus.longitude) {
                    const position = [bus.latitude, bus.longitude];
                    bounds.extend(position);
                    
                    const busColor = '#' + bus.id.toString().padStart(6, '0').substr(-6);
                    const popupContent = `
                        <b>${bus.bus_name}</b><br>
                        Bus #: ${bus.bus_number}<br>
                        Speed: ${bus.speed || 0} km/h<br>
                        Last update: ${new Date(bus.last_tracked_at || Date.now()).toLocaleTimeString()}
                    `;
                    
                    // Create or update marker
                    if (busMarkers[bus.id]) {
                        busMarkers[bus.id].setLatLng(position);
                    } else {
                        // Create marker with unique color
                        const icon = L.divIcon({
                            className: 'bus-custom-icon',
                            html: `<div style="background-color: ${busColor}; width: 24px; height: 24px; border-radius: 50%; border: 2px solid white;"></div>`,
                            iconSize: [24, 24],
                            iconAnchor: [12, 12],
                            popupAnchor: [0, -12]
                        });
                        
                        busMarkers[bus.id] = L.marker(position, {icon: icon}).addTo(map);
                        busMarkers[bus.id].bindPopup(popupContent);
                        
                        // Add click handler to highlight in list
                        busMarkers[bus.id].on('click', function() {
                            highlightBusInList(bus.id);
                        });
                    }
                    
                    // Update popup content
                    busMarkers[bus.id].getPopup().setContent(popupContent);
                    
                    // Add or update label
                    if (showLabels) {
                        if (!busMarkers[bus.id + '_label']) {
                            busMarkers[bus.id + '_label'] = L.tooltip({
                                permanent: true,
                                direction: 'top',
                                className: 'bus-label'
                            })
                            .setLatLng(position)
                            .setContent(`<strong>${bus.bus_number}</strong>`)
                            .addTo(map);
                        } else {
                            busMarkers[bus.id + '_label'].setLatLng(position);
                        }
                    } else if (busMarkers[bus.id + '_label']) {
                        map.removeLayer(busMarkers[bus.id + '_label']);
                        delete busMarkers[bus.id + '_label'];
                    }
                }
            });
            
            // Center map if there are buses and auto-center is enabled
            if (bounds.isValid() && document.getElementById('auto-center').checked) {
                map.fitBounds(bounds, {padding: [50, 50]});
            } else if (!bounds.isValid()) {
                console.warn('No valid bus positions to center the map.');
            }
        }
        
        // Function to update the bus list table
        function updateBusList(buses) {
            const tbody = document.getElementById('bus-list');
            
            // Clear existing rows
            tbody.innerHTML = '';
            
            // Add new rows
            if (buses.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-3">
                            No buses are currently being tracked.
                        </td>
                    </tr>
                `;
            } else {
                buses.forEach(bus => {
                    const row = document.createElement('tr');
                    row.setAttribute('data-bus-id', bus.id);
                    
                    row.innerHTML = `
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bus-icon me-2" style="background-color: ${'#' + bus.id.toString().padStart(6, '0').substr(-6)}"></div>
                                <div>
                                    <strong>${bus.bus_number}</strong><br>
                                    <small>${bus.bus_name}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${bus.speed > 0 ? 'bg-success' : 'bg-secondary'}">
                                ${bus.speed > 0 ? 'Moving' : 'Stopped'}
                            </span>
                        </td>
                        <td>${bus.speed || 0} km/h</td>
                        <td>
                            <a href="/buses/${bus.id}/track" class="btn btn-sm btn-info">
                                Details
                            </a>
                        </td>
                    `;
                    
                    // Add event listener to highlight on map
                    row.addEventListener('click', function() {
                        highlightBusOnMap(bus.id);
                    });
                    
                    tbody.appendChild(row);
                });
            }
            
            // Update count badge
            document.getElementById('active-bus-count').textContent = buses.length;
        }
        
        // Function to highlight a bus on the map
        function highlightBusOnMap(busId) {
            if (busMarkers[busId]) {
                busMarkers[busId].openPopup();
                map.panTo(busMarkers[busId].getLatLng());
            }
        }
        
        // Function to highlight a bus in the list
        function highlightBusInList(busId) {
            // Remove any existing highlights
            document.querySelectorAll('#bus-list tr').forEach(row => {
                row.classList.remove('highlight-row');
            });
            
            // Add highlight to selected row
            const row = document.querySelector(`#bus-list tr[data-bus-id="${busId}"]`);
            if (row) {
                row.classList.add('highlight-row');
                row.scrollIntoView({behavior: 'smooth', block: 'nearest'});
            }
        }
        
        // Initial data fetch
        fetchAllBuses();
        
        // Set up event listeners
        document.getElementById('show-labels').addEventListener('change', fetchAllBuses);
        document.getElementById('refresh-now').addEventListener('click', fetchAllBuses);
        
        // Auto-refresh toggle
        let refreshInterval;
        
        function setAutoRefresh() {
            if (document.getElementById('auto-refresh').checked) {
                refreshInterval = setInterval(fetchAllBuses, 10000); // Refresh every 10 seconds
            } else if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        document.getElementById('auto-refresh').addEventListener('change', setAutoRefresh);
        
        // Initial setup of auto-refresh
        setAutoRefresh();
        
        // Real-time update via Laravel Echo
        if (typeof Echo !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: '{!! env('PUSHER_APP_KEY') !!}',
                wsHost: window.location.hostname,
                wsPort: 6001,
                forceTLS: false,
                disableStats: true,
            });
            window.Echo.channel('bus-tracking')
                .listen('.bus.location.updated', function(e) {
                    // Update the marker for the bus in real time
                    if (busMarkers[e.id]) {
                        const position = [e.latitude, e.longitude];
                        busMarkers[e.id].setLatLng(position);
                        // Update popup content
                        const popupContent = `
                            <b>${e.bus_name}</b><br>
                            Bus #: ${e.bus_number}<br>
                            Speed: ${e.speed || 0} km/h<br>
                            Last update: ${new Date(e.last_tracked_at || Date.now()).toLocaleTimeString()}
                        `;
                        busMarkers[e.id].getPopup().setContent(popupContent);
                        // Update label if shown
                        if (busMarkers[e.id + '_label']) {
                            busMarkers[e.id + '_label'].setLatLng(position);
                        }
                    }
                    // Update the bus list row if present
                    const row = document.querySelector(`#bus-list tr[data-bus-id="${e.id}"]`);
                    if (row) {
                        row.querySelector('td:nth-child(2) .badge').textContent = (e.speed > 0 ? 'Moving' : 'Stopped');
                        row.querySelector('td:nth-child(2) .badge').className = 'badge ' + (e.speed > 0 ? 'bg-success' : 'bg-secondary');
                        row.querySelector('td:nth-child(3)').textContent = (e.speed || 0) + ' km/h';
                    }
                });
        }
    });
</script>
</body>
</html>

