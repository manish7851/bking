@extends('layouts.dashboard')
@extends('layouts.sidebar')
<head>
<style>
    #map { height: 85vh; }
    #controls {
      text-align: center;
      font-family: sans-serif;
      padding: 10px;
      background: rgba(255, 255, 255, 0.9);
    }
    button, select {
      margin: 5px;
      padding: 6px 10px;
    }
    #info {
      margin-top: 10px;
    }
    #slider { width: 100% !important; }
    #timestamp {
      position: absolute;
      top: 10px;
      left: 10px;
      background: rgba(0,0,0,0.7);
      color: white;
      padding: 6px 12px;
      border-radius: 5px;
      font-family: sans-serif;
      font-size: 14px;
      z-index: 999;
    }
</style>
</head>
@section('content')
<div class="container mt-4">
    <h2>Tracking Session #{{ $tracking->id }} for {{ $bus->bus_name }} ({{ $bus->bus_number }})</h2>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            Tracking Session Details
        </div>
        <div class="card-body">
            <p><strong>Session ID:</strong> {{ $tracking->id }}</p>
            <p><strong>Started At:</strong> {{ $tracking->started_at }}</p>
            <p><strong>Ended At:</strong> {{ $tracking->ended_at ?? 'Ongoing' }}</p>
            <p><strong>Number of Locations:</strong> {{ $locations->count() }}</p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">
            Bus Info
        </div>
        <div class="card-body">
            <p><strong>Bus Name:</strong> {{ $bus->bus_name }}</p>
            <p><strong>Bus Number:</strong> {{ $bus->bus_number }}</p>
            <p><strong>IMEI:</strong> {{ $bus->imei ?: 'Not set' }}</p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header bg-success text-white">
            Location Path
        </div>
        <div class="card-body">
            @if($locations->count() > 0)
                <div id="map" style="height: 500px;"></div>
            @else
                <div class="alert alert-warning">No location data available for this session.</div>
            @endif
        </div>
    </div>
    <a href="{{ route('buses.tracking.list', $bus->id) }}" class="btn btn-secondary">Back to Tracking Sessions</a>
    <div id="timestamp">🕒 Timestamp: --</div>

<div id="controls">
 <input type="range"
       id="slider"
       class="slider"
       min="0"
       max="0"
       value="0"
       step="1"
       oninput="sliderJump()"
    />
 <div id="info"></div>
</div>
</div>
@endsection
@php
    $path = $locations->map(function($loc) {
        return [
            'latitude' => $loc->latitude,
            'longitude' => $loc->longitude,
            'speed' => $loc->speed ?? null,
            'recorded_at' => $loc->recorded_at,
            'heading' => $loc->heading ?? null,
        ];
    })->values();
@endphp
@php
    $locationCoords = $locations->map(function($loc) {
        return [$loc->latitude, $loc->longitude];
    })->values();
@endphp

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- PolylineDecorator for arrows -->
<script src="https://unpkg.com/leaflet-polylinedecorator@1.7.0/dist/leaflet.polylineDecorator.min.js"></script>
<script src="{{ asset(path: 'js/polyline-decorator.js') }}"></script>
<script>
    var path = @json($path);
    var locations = @json($locationCoords);
    const latlngs = locations;
    let index = 0;
    let marker;
    let map;
    let slider;
    let info;
    let timestampBox;
    function interpolateTime(i) {
            const t1 = new Date(path[i].recorded_at);
            const t2 = new Date(path[i + 1].recorded_at);
            return t2 - t1; // ms
        }

        function computeSpeed(i) {
            const d = map.distance(latlngs[i], latlngs[i + 1]); // m
            const t = interpolateTime(i) / 1000; // s
            return ((d / 1000) / (t / 3600)).toFixed(1); // km/h
        }

        function computeDirection(i) {
            const [lat1, lng1] = latlngs[i];
            const [lat2, lng2] = latlngs[i + 1];
            const angle = Math.atan2(lng2 - lng1, lat2 - lat1) * (180 / Math.PI);
            return (angle + 360).toFixed(0) % 360 + "°";
        }

        function showInfo(i) {
            if (i >= path.length - 1) return;
            const t = new Date(path[i + 1].recorded_at).toLocaleTimeString();
            info.innerHTML = `🕒 ${t} &nbsp;&nbsp; 🚗 ${computeSpeed(i)} km/h &nbsp;&nbsp; ↗ ${computeDirection(i)}`;
            timestampBox.innerHTML = `🕒 ${t}`;
        }
    

        function sliderJump() {
            index = parseInt(slider.value);
            console.log(marker);
            marker.setLatLng(latlngs[index]);
            showInfo(index);
        }

  // Add .slideTo support to Leaflet markers
  document.addEventListener('DOMContentLoaded', function() {
      
        console.log(locations);
        map = L.map('map');
        if (locations.length > 0) {
            map.setView(locations[0], 14);
        } else {
            map.setView([27.7, 85.3], 8); // Default to Nepal
        }
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        var polyline = L.polyline(locations, {color: 'blue', weight: 5}).addTo(map);
        map.fitBounds(polyline.getBounds(), {padding: [30, 30]});
        // Add arrows using PolylineDecorator
        var decorator = L.polylineDecorator(polyline, {
            patterns: [
                {
                    offset: 25,
                    repeat: 50,
                    symbol: L.Symbol.arrowHead({pixelSize: 12, polygon: false, pathOptions: {stroke: true, color: 'green'}})
                }
            ]
        }).addTo(map);
        // Markers for start and end
        // if (locations.length > 0) {
        //     L.marker(locations[0], {icon: L.icon({iconUrl: 'https://cdn-icons-png.flaticon.com/512/149/149060.png', iconSize: [32,32], iconAnchor: [16,32]})}).addTo(map).bindPopup('Start');
        //     if (locations.length > 1) {
        //         L.marker(locations[locations.length-1], {icon: L.icon({iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png', iconSize: [32,32], iconAnchor: [16,32]})}).addTo(map).bindPopup('End');
        //     }
        // }
        // L.polyline(latlngs, { color: 'gray', dashArray: '5,5', weight: 2 }).addTo(map);
        // L.marker(latlngs[0]).addTo(map).bindPopup("Start");
        // L.marker(latlngs[latlngs.length - 1]).addTo(map).bindPopup("End");

        marker = L.marker(latlngs[0]).addTo(map).bindPopup("Vehicle");
        marker.setLatLng(latlngs[10])
        slider = document.getElementById("slider");
        info = document.getElementById("info");
        timestampBox = document.getElementById("timestamp");

        slider.max = path.length - 1;
    });
</script>
