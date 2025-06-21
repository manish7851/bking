@extends('layouts.app')
    <div class="container">
        <h1>Edit Route</h1>
        <form action="{{ route('routes.update', $route) }}" method="POST">
        @csrf
        @method('GET')
            <div class="mb-3">
                <label for="bus_id" class="form-label">Bus</label>
                <select class="form-control" id="bus_id" name="bus_id" required>
                    <option value="">Select a bus</option>
                    @foreach($buses as $bus)
                        <option value="{{ $bus->id }}" {{ $route->bus_id == $bus->id ? 'selected' : '' }}>{{ $bus->bus_name }} ({{ $bus->bus_number }})</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="source" class="form-label">Source</label>
                <input type="text" class="form-control" id="source" name="source" value="{{ old('source', $route->source) }}" required>
            </div>

            <div class="mb-3">
                <label for="destination" class="form-label">Destination</label>
                <input type="text" class="form-control" id="destination" name="destination" value="{{ old('destination', $route->destination) }}" required>
            </div>

            <div class="mb-3">
                <label for="trip_date" class="form-label">Trip Date</label>
                <input type="date" class="form-control" id="trip_date" name="trip_date" value="{{ old('trip_date', $route->trip_date ? \Carbon\Carbon::parse($route->trip_date)->format('Y-m-d') : '' ) }}" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $route->price) }}" required>
            </div>

            <div class="mb-3">
                <label for="source_latitude" class="form-label">Source Latitude</label>
                <input type="text" class="form-control" id="source_latitude" name="source_latitude" value="{{ old('source_latitude', $route->source_latitude) }}" required>
            </div>
            <div class="mb-3">
                <label for="source_longitude" class="form-label">Source Longitude</label>
                <input type="text" class="form-control" id="source_longitude" name="source_longitude" value="{{ old('source_longitude', $route->source_longitude) }}" required>
            </div>
            <div class="mb-3">
                <label for="destination_latitude" class="form-label">Destination Latitude</label>
                <input type="text" class="form-control" id="destination_latitude" name="destination_latitude" value="{{ old('destination_latitude', $route->destination_latitude) }}" required>
            </div>
            <div class="mb-3">
                <label for="destination_longitude" class="form-label">Destination Longitude</label>
                <input type="text" class="form-control" id="destination_longitude" name="destination_longitude" value="{{ old('destination_longitude', $route->destination_longitude) }}" required>
            </div>

            <button type="submit" class="btn btn-success">Update Route</button>
        </form>
    </div>

