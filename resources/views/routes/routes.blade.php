@extends('layouts.dashboard')

<!-- External CSS -->
<link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">
<link href="{{ asset('css/routes.css') }}" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

<div class="container mt-5 mb-5" style="margin-left: 300px; margin-top: 100px;">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h2>All Routes</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">➕ Add Route</button>
    </div>

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
                    <td>{{ $route->trip_date ? \Carbon\Carbon::parse($route->trip_date)->format('Y-m-d') : 'N/A' }}</td>
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
                            <label for="trip_date" class="form-label">Trip Date</label>
                            <input type="date" class="form-control" name="trip_date" 
                                min="{{ date('Y-m-d') }}" 
                                value="{{ date('Y-m-d') }}" 
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" required>
                        </div>
                        <div class="mb-3">
                            <label for="source_latitude" class="form-label">Source Latitude</label>
                            <input type="text" class="form-control" name="source_latitude" required>
                        </div>
                        <div class="mb-3">
                            <label for="source_longitude" class="form-label">Source Longitude</label>
                            <input type="text" class="form-control" name="source_longitude" required>
                        </div>
                        <div class="mb-3">
                            <label for="destination_latitude" class="form-label">Destination Latitude</label>
                            <input type="text" class="form-control" name="destination_latitude" required>
                        </div>
                        <div class="mb-3">
                            <label for="destination_longitude" class="form-label">Destination Longitude</label>
                            <input type="text" class="form-control" name="destination_longitude" required>
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

    <!-- External JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>