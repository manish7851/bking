@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Subscription</h1>
    <form action="{{ route('subscriptions.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="isadmin" class="form-label">Is Admin</label>
            <select name="isadmin" id="isadmin" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>
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
        <div class="mb-3">
            <label class="form-label">Alert Type</label><br>
            <input type="checkbox" name="alert_source" id="alert_source" value="1"> <label for="alert_source">Source</label>
            <input type="checkbox" name="alert_destination" id="alert_destination" value="1"> <label for="alert_destination">Destination</label>
            <input type="checkbox" name="alert_zone" id="alert_zone" value="1"> <label for="alert_zone">Alert Zone</label>
        </div>
        <div class="mb-3" id="zone-coords" style="display:none;">
            <label for="zone_latitude" class="form-label">Zone Latitude</label>
            <input type="text" name="zone_latitude" id="zone_latitude" class="form-control">
            <label for="zone_longitude" class="form-label">Zone Longitude</label>
            <input type="text" name="zone_longitude" id="zone_longitude" class="form-control">
        </div>
        <div class="mb-3">
            <label for="delivered" class="form-label">Delivered</label>
            <select name="delivered" id="delivered" class="form-control">
                <option value="0">No</option>
                <option value="1">Yes</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
<script>
    document.getElementById('alert_zone').addEventListener('change', function() {
        document.getElementById('zone-coords').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endsection
