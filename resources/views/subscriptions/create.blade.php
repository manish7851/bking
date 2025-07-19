@extends('layouts.app')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #mapModal {
      display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7); justify-content: center; align-items: center;
      z-index: 9999;
    }
    #mapContainer {
      width: 90%; height: 80%; background: #fff; display: flex; flex-direction: column; position: relative;
    }
    #map { flex-grow: 1; }
    .map-header {
      padding: 8px; background: #f5f5f5; font-size: 14px;
    }
    .map-actions {
      padding: 8px; background: #fafafa; text-align: right;
    }
</style>
@section('content')
<div class="container">
    <h1>Add Subscription</h1>
    <form action="{{ route('subscriptions.store') }}" method="POST">
        @csrf
        <input type="hidden" name="isadmin" id="isadmin" value="0" class="form-control" required/>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ session()->has('customer_id') ? Auth::guard('customer')->user()->email : '' }}" {{ session()->has('customer_id') ? 'readonly' : '' }} required>
        </div>
        @if(isset($activeRoute))
            <div class="mb-3">
                <label class="form-label">Route Details</label>
                <div class="p-2 border rounded bg-light">
                    <strong>Source:</strong> {{ $activeRoute->source }}<br>
                    <strong>Destination:</strong> {{ $activeRoute->destination }}<br>
                    <strong>Trip Date:</strong>
                    @php
                        $dateObj = \Carbon\Carbon::parse($activeRoute->trip_date);
                        $formattedDate = $dateObj->format('F j, Y');
                    @endphp
                    {{ $formattedDate }}
                </div>
                <input type="hidden" name="route_id" id="route_id" value="{{ $activeRoute->id }}">
            </div>
        @else
            <div class="mb-3">
                <label for="route_id" class="form-label">Route</label>
                <select name="route_id" id="route_id" class="form-control" required>
                    <option value="">Select Route</option>
                    @foreach($routes as $route)
                        <option value="{{ $route->id }}" data-source-lat="{{ $route->source_latitude }}" data-source-lng="{{ $route->source_longitude }}" data-dest-lat="{{ $route->destination_latitude }}" data-dest-lng="{{ $route->destination_longitude }}" data-bus-id="{{ $route->bus_id }}">
                            {{ $route->routeName }} (Bus: {{ $route->bus->bus_name ?? $route->bus_id }})
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        
        <div class="mb-3">
            <label class="form-label">Alert Type</label><br>
            <input type="checkbox" name="alert_source" id="alert_source" value="1"> <label for="alert_source">Source</label>
            <input type="checkbox" name="alert_destination" id="alert_destination" value="1"> <label for="alert_destination">Destination</label>
            <input type="checkbox" name="alert_zone" id="alert_zone" value="1"> <label for="alert_zone">Alert Zone</label>
        </div>
        <div class="mb-3" id="zone-coords" style="display:none;">
            <label>Alert Zone Address Search:</label><br>
            <input type="text" id="address" placeholder="Search or pick on map..." autocomplete="off"><br><br>
            OR 
            <input type="text" name="zone_latitude" id="zone_latitude" class="form-control">
            <label for="zone_longitude" class="form-label">Zone Longitude</label>
            <button type="button" onclick="openMap()">Pick on Map</button><br/>
            <label for="zone_latitude" class="form-label">Zone Latitude</label>
            <input type="text" name="zone_longitude" id="zone_longitude" class="form-control">
            <input type="hidden" name="alert_zone_address" id="alert_zone_address">
        </div>
        <input type="hidden" name="message" id="message" value="" class="form-control">
        <input type="hidden" name="delivered" id="delivered" value="0">
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>

<!-- Map Modal -->
<div id="mapModal">
  <div id="mapContainer">
    <div class="map-header">
      <strong>Address:</strong> <span id="selectedAddress">Click on the map</span><br>
      <strong>Coordinates:</strong> <span id="selectedCoords">-</span>
    </div>
    <div id="map"></div>
    <div class="map-actions">
        <button type="button" onclick="confirmLocation()">Pick on Map</button>
        <button type="button" onclick="closeMap()">Close</button>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    const routes = @json($routes);
    const activeRoute = @json($activeRoute ?? null);
    function formattedDate(tripDate) {
        if (tripDate) {
            const dateObj = new Date(tripDate);
            return dateObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
        }
        return '';
    }
    if (activeRoute) {
        document.getElementById('route_id').value = activeRoute.id;
        document.getElementById('message').value = `From ${activeRoute.source} to ${activeRoute.destination} on ${activeRoute.trip_date}`;
        document.getElementById('message').value =
            `From ${activeRoute.source} to ${activeRoute.destination} on ${formattedDate(activeRoute.trip_date)}`;

    } else {
    document.getElementById('route_id').addEventListener('change', function() {
        const selected = document.getElementById('route_id').value;
        const selectedRoute = routes.find(r => r.id == selected);
        if (!selectedRoute) {
            document.getElementById('message').value = '';
            return;
        }
        const source = selectedRoute.source;
        const destination = selectedRoute.destination;
        let tripDate = selectedRoute.trip_date;
        // Try to get trip date from a date input if available, otherwise use today
        // Format tripDate as human readable (e.g., YYYY-MM-DD to "June 5, 2024")
        document.getElementById('message').value =
            `From ${source} to ${destination} on ${formattedDate(tripDate)}`;
    });
    }

    document.getElementById('alert_zone').addEventListener('change', function() {
        document.getElementById('zone-coords').style.display = this.checked ? 'block' : 'none';
    });
    let map, marker, selectedLatLng = null, selectedAddr = '';

    function openMap() {
    document.getElementById('mapModal').style.display = 'flex';

    let lat = parseFloat(document.getElementById('zone_latitude').value);
    let lng = parseFloat(document.getElementById('zone_longitude').value);

    // If no coordinates provided, use default location
    if (isNaN(lat) || isNaN(lng)) {
        lat = 28.127867967261864; lng = 82.29569223574006;
    }

    if (!map) {
        map = L.map('map').setView([lat, lng], 17);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', () => updateAddress(marker.getLatLng()));

        map.on('click', e => {
        marker.setLatLng(e.latlng);
        updateAddress(e.latlng);
        });
    } else {
        map.setView([lat, lng], 13);
        marker.setLatLng([lat, lng]);
    }

    updateAddress({ lat, lng });
    }

    function updateAddress(latlng) {
    selectedLatLng = latlng;
    document.getElementById('selectedCoords').textContent = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;

    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latlng.lat}&lon=${latlng.lng}`)
        .then(res => res.json())
        .then(data => {
        selectedAddr = data.display_name || 'Unknown location';
        document.getElementById('selectedAddress').textContent = selectedAddr;
        })
        .catch(() => {
        selectedAddr = 'Unknown location';
        document.getElementById('selectedAddress').textContent = selectedAddr;
        });
    }

    function confirmLocation() {
    if (selectedLatLng) {
        document.getElementById('address').value = selectedAddr;
        document.getElementById('zone_latitude').value = selectedLatLng.lat.toFixed(6);
        document.getElementById('zone_longitude').value = selectedLatLng.lng.toFixed(6);
        document.getElementById('alert_zone_address').value = selectedAddr;
    }
    closeMap();
    }

    function closeMap() {
    document.getElementById('mapModal').style.display = 'none';
    }

    // Simple autocomplete
    const addressInput = document.getElementById('address');
    addressInput.addEventListener('input', function() {
    const query = this.value;
    if (query.length < 3) return;
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
        if (data[0]) {
            document.getElementById('zone_latitude').value = data[0].lat;
            document.getElementById('zone_longitude').value = data[0].lon;
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
        document.getElementById('alert_zone_address').value = div.textContent;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(div.textContent)}`)
            .then(res => res.json())
            .then(data => {
            if (data[0]) {
                document.getElementById('latitude').value = data[0].lat;
                document.getElementById('longitude').value = data[0].lon;

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

</script>
@endsection
