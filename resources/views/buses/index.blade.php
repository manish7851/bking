@extends('layouts.dashboard')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Management</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link href="{{ asset('css/bus.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
</head>
<body>

@extends('layouts.sidebar')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-2 mt-4">
            <h1>Bus Management</h1>

            <!-- Success Message -->
            @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif
            
            <!-- Add Bus Button -->
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBusModal">
                    ➕ Add Bus
                </button>
            </div>

            <!-- Add Bus Modal -->
            <div class="modal fade" id="addBusModal" tabindex="-1" aria-labelledby="addBusModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="busForm" method="POST" action="{{ route('buses.store') }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="addBusModalLabel">Add New Bus</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="bus_name" class="form-label">Bus Name</label>
                                    <input type="text" class="form-control" id="bus_name" name="bus_name" required>
                                </div>                                <div class="mb-3">
                                    <label for="bus_number" class="form-label">Bus Number</label>
                                    <input type="text" class="form-control" id="bus_number" name="bus_number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="imei" class="form-label">IMEI Number</label>
                                    <input type="text" class="form-control" id="imei" name="imei" 
                                           pattern="[0-9]{15,17}" title="Please enter a valid 15-17 digit IMEI number">
                                    <small class="form-text text-muted">Enter the 15-17 digit IMEI number of the tracking device</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Add Bus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bus List -->
            <h3>Bus List</h3>
            <div class="table-responsive">                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>                            <th>ID</th>
                            <th>Bus Name</th>
                            <th>Bus Number</th>
                            <th>IMEI</th>
                            <!-- <th>Location</th> -->
                            <th>Active Trip</th>
                            <!-- <th>Last Update</th> -->
                            <th>Tracking Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($buses as $bus)
                        <tr>                            <td>{{ $bus->id }}</td>
                            <td>{{ $bus->bus_name }}</td>
                            <td>{{ $bus->bus_number }}</td>
                            <td>{{ $bus->imei ?: 'Not set' }}</td>
                            <!-- <td>
                                @if($bus->latitude && $bus->longitude)
                                    {{ number_format($bus->latitude, 5) }}, {{ number_format($bus->longitude, 5) }}
                                @else
                                    <span class="text-muted">No data</span>
                                @endif
                            </td> -->
                            <td>                                @php
                                    $activeRoute = $bus->routes()
                                        ->whereDate('trip_date', '>=', now()->format('Y-m-d'))
                                        ->orderBy('trip_date')
                                        ->first();
                                @endphp
                                @if($activeRoute)
                                    @php
                                        $tripDate = \Carbon\Carbon::parse($activeRoute->trip_date);
                                        $isToday = $tripDate->isToday();
                                    @endphp
                                    <span class="{{ $isToday ? 'text-success' : 'text-primary' }}">
                                        {{ $activeRoute->source }} → {{ $activeRoute->destination }}
                                        <br>
                                        <small>
                                            @if($isToday)
                                                Today
                                            @else
                                                {{ $tripDate->format('M d, Y') }}
                                            @endif
                                        </small>
                                    </span>
                                @else
                                    <span class="text-muted">No upcoming trips</span>
                                @endif
                            </td>
                            <!-- <td>
                                @if($bus->last_tracked_at)
                                    {{ \Carbon\Carbon::parse($bus->last_tracked_at)->diffForHumans() }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td> -->
                            <td>
                                <span class="badge {{ $bus->tracking_enabled ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $bus->tracking_enabled ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('buses.edit', $bus->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="{{ route('buses.track', $bus->id) }}" class="btn btn-info btn-sm">Map</a>
                                    <form action="{{ route('buses.tracking', $bus->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $bus->tracking_enabled ? 'btn-warning' : 'btn-success' }} btn-sm">
                                            {{ $bus->tracking_enabled ? 'Stop Tracking' : 'Start Tracking' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('buses.destroy', $bus->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this bus?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function showModal() {
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('confirmModal').style.display = 'none';
    }

    function submitBusForm() {
        document.getElementById('busForm').submit();
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>
</html>
