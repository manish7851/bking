@extends('layouts.user-sidebar')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
 
<div class="container">
    <h1 class="mb-4">Search for Routes</h1>

    <form method="GET" action="{{ route('userbookings.search') }}" class="mb-4">
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
        </div>
    </form>

    @php
        $bookings = $bookings ?? collect();
        $routes = $routes ?? collect();
    @endphp

    @if($routes->isNotEmpty())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Bus Name</th>
                    <th>Bus Number</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Services</th>
                    <th>Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($routes as $route)
                    <tr>
                        <td>{{ $route->bus->bus_name ?? 'N/A' }}</td>
                        <td>{{ $route->bus->bus_number ?? 'N/A' }}</td>
                        <td>{{ $route->source }}</td>
                        <td>{{ $route->destination }}</td>
                        <td>
                            @php
                                $services = [];
                                $busIdentifier = strtolower($route->bus->bus_name ?? '');

                                if (str_contains($busIdentifier, 'express')) {
                                    $services[] = 'WiFi';
                                    $services[] = 'Air Conditioning';
                                    $services[] = 'Fast Service';
                                }

                                if (str_contains($busIdentifier, 'luxury') || str_contains($busIdentifier, 'premium')) {
                                    $services[] = 'WiFi';
                                    $services[] = 'Air Conditioning';
                                    $services[] = 'Reclining Seats';
                                    $services[] = 'Entertainment';
                                    $services[] = 'Refreshments';
                                }

                                if (empty($services)) {
                                    $services[] = 'Standard Seating';
                                    $services[] = 'Air Conditioning';
                                }
                            @endphp                            {{ implode(', ', $services) }}
                        </td>
                        <td>Rs{{ number_format($route->price, 2) }}</td>
                        <td>
                            <a href="{{ route('userbookings.create', ['route_id' => $route->id]) }}" class="btn btn-primary btn-sm">Book Now</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

