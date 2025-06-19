 

@extends('layouts.app')

 
    <div class="container">
        <h1>Edit Bus</h1>
        @dd($bus) {{-- Check if $bus is loaded --}}
        <form action="{{ route('buses.update', $bus->id) }}" method="POST">
        @csrf
        @method('PUT')

            <div class="mb-3">
                <label for="bus_name" class="form-label">Bus Name</label>
                <input type="text" class="form-control" id="bus_name" name="bus_name" value="{{ old('bus_name', $bus->bus_name) }}" required>
            </div>

            <div class="mb-3">
                <label for="bus_number" class="form-label">Bus Number</label>
                <input type="text" class="form-control" id="bus_number" name="bus_number" value="{{ old('bus_number', $bus->bus_number) }}" required>
            </div>

            <button type="submit" class="btn btn-success">Update Bus</button>
        </form>
    </div>
@endsection
