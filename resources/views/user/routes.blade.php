@extends('layouts.user-sidebar')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

<div class="container">
    <h1 class="mb-4">Available Routes</h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Source</th>
                <th>Destination</th>
                <th>Price</th>
                <th>Bus Name</th>
                <th>Bus Number</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($routes as $route)
                <tr>
                    <td>{{ $route['source'] }}</td>
                    <td>{{ $route['destination'] }}</td>
                    <td>Rs. {{ $route['price'] }}</td>
                    <td>{{ $route['bus']['bus_name'] }}</td>
                    <td>{{ $route['bus']['bus_number'] }}</td>
                    <td>{{ ucfirst($route['bus']['status']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
