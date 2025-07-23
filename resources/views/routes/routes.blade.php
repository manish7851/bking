@extends('layouts.dashboard')

<!-- External CSS -->
<link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
<link href="{{ asset('css/routes.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapModal1 {
      display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7); justify-content: center; align-items: center;
      z-index: 9999;
    }
    #mapContainer1 {
      width: 90%; height: 80%; background: #fff; display: flex; flex-direction: column; position: relative;
    }
    #map1 { flex-grow: 1; }
    .map-header1 {
      padding: 8px; background: #f5f5f5; font-size: 14px;
    }
    .map-actions1 {
      padding: 8px; background: #fafafa; text-align: right;
    }
</style>
<style>
    #mapModal2 {
      display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7); justify-content: center; align-items: center;
      z-index: 9999;
    }
    #mapContainer2 {
      width: 90%; height: 80%; background: #fff; display: flex; flex-direction: column; position: relative;
    }
    #map2 { flex-grow: 1; }
    .map-header2 {
      padding: 8px; background: #f5f5f5; font-size: 14px;
    }
    .map-actions2 {
      padding: 8px; background: #fafafa; text-align: right;
    }
</style>
<div class="container mt-5 mb-5" style="margin-left: 300px; margin-top: 100px;">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2>All Routes</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">➕ Add Route</button>
    </div>
    <form method="GET" action="{{ route('routes.search') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="source" class="form-label">Source</label>
                <input type="text" name="source" id="source" class="form-control" placeholder="Enter source" value="{{ request('source') }}">
            </div>
            <div class="col-md-4">
                <label for="destination" class="form-label">Destination</label>
                <input type="text" name="destination" id="destination" class="form-control" placeholder="Enter destination" value="{{ request('destination') }}">
            </div>
            <div class="col-md-4">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
            </div>
        </div>
        <div class="text-end mt-3">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="/routes/search">Clear</a>
        </div>
    </form>

    <!-- Route Table -->
    <!-- Route Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Bus Name</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Coordinates</th>
                    <th>Trip Date</th>
                    <th>Price</th>
                    <th style="width: 20%;">Actions</th> <!-- Adjust the width of the Actions column -->
                </tr>
            </thead>
            <tbody>
                @foreach ($routes as $route)
                <tr>
                    <td>{{ $route->bus->bus_name ?? 'N/A' }}</td>
                    <td>{{ $route->source }}</td>
                    <td>{{ $route->destination }}</td>
                    <td>{{ $route->source_latitude }}, {{ $route->source_longitude }} - {{ $route->destination_latitude }}, {{ $route->destination_longitude }}</td>
                    <td>{{ $route->trip_date ? \Carbon\Carbon::parse($route->trip_date)->format('d M Y H:i') : 'N/A' }}</td>
                    <td>Rs{{ $route->price }}</td>
                    <td>
                        <!-- Edit Button -->
                        <a href="{{ route('routes.edit', $route) }}" class="btn btn-warning btn-sm">Edit</a>
                        <!-- Delete Button -->
                        <form action="{{ route('routes.destroy', $route) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this route?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>


    <!-- Add Route Modal -->
    <div class="modal fade" id="addRouteModal" tabindex="-1" aria-labelledby="addRouteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('routes.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addRouteModalLabel">Add Route</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="bus_id" class="form-label">Bus</label>
                            <select class="form-control" name="bus_id" required>
                                <option value="">Select a bus</option>
                                @foreach($buses as $bus)
                                    <option value="{{ $bus->id }}">{{ $bus->bus_name }} ({{ $bus->bus_number }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="source" class="form-label">Source</label>
                            <input type="text" class="form-control" name="source" required>
                        </div>

                        <div class="mb-3">
                            <label for="destination" class="form-label">Destination</label>
                            <input type="text" class="form-control" name="destination" required>
                        </div>

                        <div class="mb-3">
                            <label for="trip_date" class="form-label">Trip Date & Time</label>
                            <input type="datetime-local" class="form-control" name="trip_date" 
                                min="{{ date('Y-m-d\TH:i') }}" 
                                value="{{ date('Y-m-d\TH:i') }}" 
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" required>
                        </div>
        
                        <button type="button" onclick="openMap(1)">Pick on Map</button>    
                        <div class="mb-3">
                            <label for="source_latitude" class="form-label">Source Latitude</label>
                            <input type="text" class="form-control" name="source_latitude" id="source_latitude" required>
                        </div>
                        <div class="mb-3">
                            <label for="source_longitude" class="form-label">Source Longitude</label>
                            <input type="text" class="form-control" name="source_longitude" id="source_longitude" required>
                        </div>
                        <button type="button" onclick="openMap(2)">Pick on Map</button>    
                        
                        <div class="mb-3">
                            <label for="destination_latitude" class="form-label">Destination Latitude</label>
                            <input type="text" class="form-control" name="destination_latitude" id="destination_latitude" required>
                        </div>
                        <div class="mb-3">
                            <label for="destination_longitude" class="form-label">Destination Longitude</label>
                            <input type="text" class="form-control" name="destination_longitude" id="destination_longitude" required>
                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save</button>
                    </div>


                </form>
            </div>
        </div>
    </div>
    <div id="mapModal1">
    <div id="mapContainer1">
        <div class="map-header1">
        <strong>Address:</strong> <span id="selectedAddress1">Click on the map</span><br>
        <strong>Coordinates:</strong> <span id="selectedCoords1">-</span>
        </div>
        <div id="map1"></div>
        <div class="map-actions1">
            <button type="button" onclick="confirmLocation(1)">Pick on Map</button>
            <button type="button" onclick="closeMap(1)">Close</button>
        </div>
    </div>
    </div>
    
    <div id="mapModal2">
    <div id="mapContainer2">
        <div class="map-header1">
        <strong>Address:</strong> <span id="selectedAddress2">Click on the map</span><br>
        <strong>Coordinates:</strong> <span id="selectedCoords2">-</span>
        </div>
        <div id="map2"></div>
        <div class="map-actions2">
            <button type="button" onclick="confirmLocation(2)">Pick on Map</button>
            <button type="button" onclick="closeMap(2)">Close</button>
        </div>
    </div>
    </div>
    <!-- External JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    let map1, marker1, selectedLatLng1 = null, selectedAddr1 = '';
    let map2, marker2, selectedLatLng2 = null, selectedAddr2 = '';

    function openMap(number) {
    document.getElementById(`mapModal${number}`).style.display = 'flex';

    let lat1 = parseFloat(document.getElementById(`source_latitude`).value);
    let lng1 = parseFloat(document.getElementById(`source_longitude`).value);
    let lat2 = parseFloat(document.getElementById(`destination_latitude`).value);
    let lng2 = parseFloat(document.getElementById(`destination_longitude`).value);

    // If no coordinates provided, use default location
    if (isNaN(lat1) || isNaN(lng1)) {
        lat1 = 27.7; lng1 = 85.3;
    }
    if(isNaN(lat2) || isNaN(lng2)) {
        lat2 = 27.7; lng2 = 85.3;
    }

    if (number == 1 ? !map1: !map2) {
        if(number == 1 && !map1) {
            map1 = L.map(`map${number}`).setView([lat1, lng1], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map1);
            marker1 = L.marker([lat1, lng1], { draggable: true }).addTo(map1);
            marker1.on('dragend', () => updateAddress(marker1.getLatLng(), 1));
            map1.on('click', e => {
                marker1.setLatLng(e.latlng);
                updateAddress(e.latlng, 1);
            });
        } else if(!map2){
            map2 = L.map(`map${number}`).setView([lat2, lng2], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map2);
            marker2 = L.marker([lat2, lng2], { draggable: true }).addTo(map2);
            marker2.on('dragend', () => updateAddress(marker2.getLatLng(), 2));
            map2.on('click', e => {
                marker2.setLatLng(e.latlng);
                updateAddress(e.latlng, 2);
            });
        }        
    } else if(number == 1) {
        map1.setView([lat1, lng1], 13);
        marker1.setLatLng([lat1, lng1]);
        updateAddress({ lat:lat1, lng: lng1 }, 1);

    } else {
        map2.setView([lat2, lng2], 13);
        marker2.setLatLng([lat2, lng2]);
        updateAddress({ lat:lat2, lng: lng2 }, 2);
    }
    }

    function updateAddress(latlng, number) {
        if(number == 1) {
            selectedLatLng1 = latlng;
        } else if(number == 2) {
            selectedLatLng2 = latlng;
        }
        document.getElementById(`selectedCoords${number}`).textContent = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;

    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latlng.lat}&lon=${latlng.lng}`)
        .then(res => res.json())
        .then(data => {
        selectedAddr = data.display_name || 'Unknown location';
        document.getElementById(`selectedAddress${number}`).textContent = selectedAddr;
        })
        .catch(() => {
        selectedAddr = 'Unknown location';
        document.getElementById(`selectedAddress${number}`).textContent = selectedAddr;
        });
    }

    function confirmLocation(number) {
        if(number == 1) {
            document.getElementById(`source_latitude`).value = selectedLatLng1.lat.toFixed(6);
            document.getElementById(`source_longitude`).value = selectedLatLng1.lng.toFixed(6);
        } else if(number == 2) {
            document.getElementById(`destination_latitude`).value = selectedLatLng2.lat.toFixed(6);
            document.getElementById(`destination_longitude`).value = selectedLatLng2.lng.toFixed(6);
        }
        closeMap(number);
    }

    function closeMap(number) {
    document.getElementById(`mapModal${number}`).style.display = 'none';
    }

    
</script>