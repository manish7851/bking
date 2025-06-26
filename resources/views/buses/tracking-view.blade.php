@extends('layouts.dashboard')
@extends('layouts.sidebar')

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
</div>
@endsection

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
    document.addEventListener('DOMContentLoaded', function() {
        var locations = @json($locationCoords);
        console.log(locations);
        var map = L.map('map');
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
        if (locations.length > 0) {
            L.marker(locations[0], {icon: L.icon({iconUrl: 'https://cdn-icons-png.flaticon.com/512/149/149060.png', iconSize: [32,32], iconAnchor: [16,32]})}).addTo(map).bindPopup('Start');
            if (locations.length > 1) {
                L.marker(locations[locations.length-1], {icon: L.icon({iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png', iconSize: [32,32], iconAnchor: [16,32]})}).addTo(map).bindPopup('End');
            }
        }
    });
</script>
