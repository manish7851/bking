<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bus</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="{{ asset('css/bus.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
</head>
<body>

@extends('layouts.sidebar')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-2 mt-4">
            <h1>Edit Bus</h1>

            <!-- Success Message -->
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            <!-- Back to List Button -->
            <div class="mb-4">
                <a href="{{ route('buses.index') }}" class="btn btn-secondary">
                    ← Back to Bus List
                </a>
            </div>

            <!-- Edit Form -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('buses.update', $bus->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="bus_name" class="form-label">Bus Name</label>
                            <input type="text" class="form-control" id="bus_name" name="bus_name" value="{{ $bus->bus_name }}" required>
                        </div>                        <div class="mb-3">
                            <label for="bus_number" class="form-label">Bus Number</label>
                            <input type="text" class="form-control" id="bus_number" name="bus_number" value="{{ $bus->bus_number }}" required>
                        </div>
                        
                        <h4 class="mt-4 mb-3">GPS Tracking Settings</h4>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="tracking_enabled" name="tracking_enabled" value="1" {{ $bus->tracking_enabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="tracking_enabled">Enable GPS Tracking</label>
                        </div>
                        
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Current Location Information</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Latitude:</strong> {{ $bus->latitude ? number_format($bus->latitude, 6) : 'Not available' }}</p>
                                        <p><strong>Longitude:</strong> {{ $bus->longitude ? number_format($bus->longitude, 6) : 'Not available' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Speed:</strong> {{ $bus->speed ? $bus->speed . ' km/h' : 'Not available' }}</p>
                                        <p><strong>Last Update:</strong> {{ $bus->last_tracked_at ? \Carbon\Carbon::parse($bus->last_tracked_at)->format('M d, Y H:i:s') : 'Never' }}</p>
                                    </div>
                                </div>
                                @if($bus->latitude && $bus->longitude)
                                <div class="text-center mt-2">
                                    <a href="{{ route('buses.track', $bus->id) }}" class="btn btn-info">View on Map</a>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Update Bus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>
