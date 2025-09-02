@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-route me-2"></i>Bus Routes Management</h5>
                    <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addRouteModal">
                        <i class="fas fa-plus me-2"></i>Add New Route
                    </button>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Search Form -->
                    <form action="{{ route('routes.search') }}" method="GET" class="mb-4 bg-light p-3 rounded">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="source" class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Source</label>
                                    <input type="text" name="source" id="source" class="form-control" placeholder="Enter source location" value="{{ request('source') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="destination" class="form-label"><i class="fas fa-map-marker me-2"></i>Destination</label>
                                    <input type="text" name="destination" id="destination" class="form-control" placeholder="Enter destination" value="{{ request('destination') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date" class="form-label"><i class="fas fa-calendar me-2"></i>Trip Date</label>
                                    <input type="date" name="date" id="date" class="form-control" value="{{ request('date') }}">
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-group w-100">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Search Routes
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <a href="{{ route('routes.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-redo me-2"></i>Reset Filters
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Routes Table -->
                    <div class="table-responsive mt-3">
                        <table class="table table-hover table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center"><i class="fas fa-bus me-2"></i>Bus Number</th>
                                    <th><i class="fas fa-map-marker-alt me-2"></i>Source</th>
                                    <th><i class="fas fa-map-marker me-2"></i>Destination</th>
                                    <th class="text-center"><i class="fas fa-money-bill me-2"></i>Price</th>
                                    <th class="text-center"><i class="fas fa-calendar-alt me-2"></i>Trip Date</th>
                                    <th class="text-center"><i class="fas fa-cogs me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($routes as $route)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $route->bus->bus_number ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $route->source }}</strong>
                                            @if($route->source_latitude && $route->source_longitude)
                                                <small class="d-block text-muted">
                                                    <i class="fas fa-location-dot me-1"></i>
                                                    {{ number_format($route->source_latitude, 6) }}, {{ number_format($route->source_longitude, 6) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $route->destination }}</strong>
                                            @if($route->destination_latitude && $route->destination_longitude)
                                                <small class="d-block text-muted">
                                                    <i class="fas fa-location-dot me-1"></i>
                                                    {{ number_format($route->destination_latitude, 6) }}, {{ number_format($route->destination_longitude, 6) }}
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                Rs. {{ number_format($route->price, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div>{{ \Carbon\Carbon::parse($route->trip_date)->format('Y-m-d') }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($route->trip_date)->format('h:i A') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('routes.edit', $route->id) }}" class="btn btn-sm btn-primary" title="Edit Route">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('routes.destroy', $route->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this route?')" title="Delete Route">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('routes.track', $route->id) }}" class="btn btn-sm btn-info" title="Track Route">
                                                    <i class="fas fa-location-dot"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-info-circle me-2"></i>No routes found
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1" aria-labelledby="addRouteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addRouteModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add New Route
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('routes.store') }}" method="POST" id="addRouteForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bus_id" class="form-label">
                                    <i class="fas fa-bus me-2"></i>Select Bus
                                </label>
                                <select class="form-select" id="bus_id" name="bus_id" required>
                                    <option value="">Choose a bus...</option>
                                    @foreach($buses as $bus)
                                        <option value="{{ $bus->id }}">{{ $bus->bus_number }} - {{ $bus->bus_type ?? 'Standard' }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Please select a bus.</div>
                            </div>

                            <div class="mb-3">
                                <label for="source" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Source Location
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="source" name="source" 
                                           placeholder="Enter source location" required>
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="hidden" id="source_latitude" name="source_latitude">
                                <input type="hidden" id="source_longitude" name="source_longitude">
                                <small class="text-muted">Start typing to search locations</small>
                            </div>

                            <div class="mb-3">
                                <label for="destination" class="form-label">
                                    <i class="fas fa-map-marker me-2"></i>Destination Location
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="destination" name="destination" 
                                           placeholder="Enter destination location" required>
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="hidden" id="destination_latitude" name="destination_latitude">
                                <input type="hidden" id="destination_longitude" name="destination_longitude">
                                <small class="text-muted">Start typing to search locations</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">
                                    <i class="fas fa-money-bill me-2"></i>Ticket Price
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rs.</span>
                                    <input type="number" class="form-control" id="price" name="price" 
                                           min="0" step="0.01" required placeholder="0.00">
                                </div>
                                <div class="invalid-feedback">Please enter a valid price.</div>
                            </div>

                            <div class="mb-3">
                                <label for="trip_date" class="form-label">
                                    <i class="fas fa-calendar-alt me-2"></i>Trip Date & Time
                                </label>
                                <input type="datetime-local" class="form-control" id="trip_date" 
                                       name="trip_date" required>
                                <div class="invalid-feedback">Please select a valid date and time.</div>
                            </div>

                            <div id="map" class="border rounded" style="height: 200px;">
                                <!-- Map will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Route
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .table td, .table th {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin: 0 2px;
    }
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.8em;
    }
</style>
@endpush

@push('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&libraries=places"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let map;
    let markers = [];
    let directionsService;
    let directionsRenderer;

    // Initialize the map
    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 27.7172, lng: 85.3240 }, // Default center (Kathmandu)
            zoom: 8
        });

        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            map: map
        });

        // Initialize autocomplete for source and destination
        initializeAutocomplete();
    }

    function initializeAutocomplete() {
        const sourceInput = document.getElementById('source');
        const destinationInput = document.getElementById('destination');
        
        const sourceAutocomplete = new google.maps.places.Autocomplete(sourceInput);
        const destinationAutocomplete = new google.maps.places.Autocomplete(destinationInput);

        sourceAutocomplete.addListener('place_changed', function() {
            const place = sourceAutocomplete.getPlace();
            handlePlaceSelection(place, 'source');
        });

        destinationAutocomplete.addListener('place_changed', function() {
            const place = destinationAutocomplete.getPlace();
            handlePlaceSelection(place, 'destination');
        });
    }

    function handlePlaceSelection(place, type) {
        if (!place.geometry) {
            alert('Please select a valid location from the dropdown.');
            return;
        }

        // Set hidden inputs
        document.getElementById(type + '_latitude').value = place.geometry.location.lat();
        document.getElementById(type + '_longitude').value = place.geometry.location.lng();

        // Update map
        updateMapMarkers();
    }

    function updateMapMarkers() {
        // Clear existing markers
        markers.forEach(marker => marker.setMap(null));
        markers = [];

        const sourceLat = document.getElementById('source_latitude').value;
        const sourceLng = document.getElementById('source_longitude').value;
        const destLat = document.getElementById('destination_latitude').value;
        const destLng = document.getElementById('destination_longitude').value;

        if (sourceLat && sourceLng) {
            const sourceMarker = new google.maps.Marker({
                position: { lat: parseFloat(sourceLat), lng: parseFloat(sourceLng) },
                map: map,
                title: 'Source',
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                }
            });
            markers.push(sourceMarker);
        }

        if (destLat && destLng) {
            const destMarker = new google.maps.Marker({
                position: { lat: parseFloat(destLat), lng: parseFloat(destLng) },
                map: map,
                title: 'Destination',
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                }
            });
            markers.push(destMarker);
        }

        // If both points are set, show route
        if (sourceLat && sourceLng && destLat && destLng) {
            const request = {
                origin: { lat: parseFloat(sourceLat), lng: parseFloat(sourceLng) },
                destination: { lat: parseFloat(destLat), lng: parseFloat(destLng) },
                travelMode: google.maps.TravelMode.DRIVING
            };

            directionsService.route(request, function(result, status) {
                if (status == 'OK') {
                    directionsRenderer.setDirections(result);
                    // Update distance and duration if needed
                    const route = result.routes[0];
                    if (route.legs[0]) {
                        const distance = route.legs[0].distance.text;
                        const duration = route.legs[0].duration.text;
                        // You can display this information somewhere in your form
                    }
                }
            });
        }
    }

    // Form validation
    const form = document.getElementById('addRouteForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Initialize map when modal is shown
    const addRouteModal = document.getElementById('addRouteModal');
    addRouteModal.addEventListener('shown.bs.modal', function() {
        initMap();
    });

    // Set minimum date for trip_date to today
    const tripDateInput = document.getElementById('trip_date');
    const today = new Date();
    const todayStr = today.toISOString().slice(0, 16);
    tripDateInput.min = todayStr;
    tripDateInput.value = todayStr;
});
</script>
@endpush
@endsection
