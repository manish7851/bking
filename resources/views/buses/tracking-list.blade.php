@extends('layouts.dashboard')
@extends('layouts.sidebar')

@section('content')
<div class="container mt-4">
    <h2>Tracking Sessions for {{ $bus->bus_name }} ({{ $bus->bus_number }})</h2>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white" style="cursor:pointer;" data-bs-toggle="collapse" data-bs-target="#busInfoCollapse" aria-expanded="false" aria-controls="busInfoCollapse">
            Bus Details <span class="float-end"><i class="bi bi-chevron-down"></i></span>
        </div>
        <div class="collapse show" id="busInfoCollapse">
            <div class="card-body">
                <p><strong>Bus Name:</strong> {{ $bus->bus_name }}</p>
                <p><strong>Bus Number:</strong> {{ $bus->bus_number }}</p>
                <p><strong>IMEI:</strong> {{ $bus->imei ?: 'Not set' }}</p>
                <p><strong>Status:</strong> <span class="badge {{ $bus->tracking_enabled ? 'bg-success' : 'bg-secondary' }}">{{ $bus->tracking_enabled ? 'Tracking Active' : 'Tracking Disabled' }}</span></p>
                <p><strong>Last Update:</strong> {{ $bus->last_tracked_at ? $bus->last_tracked_at : 'Never' }}</p>
            </div>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Started At</th>
                <th>Ended At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trackings as $tracking)
            <tr>
                <td>{{ $tracking->id }}</td>
                <td>{{ $tracking->started_at }}</td>
                <td>{{ $tracking->ended_at ?? 'Ongoing' }}</td>
                <td>
                    <a href="{{ route('buses.tracking.show', [$bus->id, $tracking->id]) }}" class="btn btn-info btn-sm">View</a>
                    <form action="{{ route('buses.tracking.delete', [$bus->id, $tracking->id]) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Delete this tracking session?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('buses.index') }}" class="btn btn-secondary mt-3">Back to Bus List</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.js"></script>
@endsection
