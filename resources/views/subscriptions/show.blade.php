@extends('layouts.app')
@section('content')
<div class="container">
    <h1>Subscription Details</h1>
    <div class="mb-3">
        <strong>ID:</strong> {{ $subscription->id }}
    </div>
    <div class="mb-3">
        <strong>Is Admin:</strong> {{ $subscription->isadmin ? 'Yes' : 'No' }}
    </div>
    <div class="mb-3">
        <strong>Email:</strong> {{ $subscription->email }}
    </div>
    <div class="mb-3">
        <strong>Alert Type:</strong> {{ $subscription->alert->type ?? '-' }}
    </div>
    <div class="mb-3">
        <strong>Bus:</strong> {{ $subscription->alert->bus->bus_name ?? '-' }}
    </div>
    <div class="mb-3">
        <strong>Route:</strong> {{ $subscription->alert->message ?? 'N/A' }}    
    </div>
    <div class="mb-3">
        <strong>Location Name:</strong>
        {{ $subscription->alert ? $subscription->alert->location_name : '-' }}
    </div>
    <div class="mb-3">
        <strong>Latitude:</strong> {{ $subscription->alert->latitude ?? '-' }}
    </div>
    <div class="mb-3">
        <strong>Longitude:</strong> {{ $subscription->alert->longitude ?? '-' }}
    </div>
    <!-- <div class="mb-3">
        <strong>Delivered:</strong> {{ $subscription->delivered ? 'Yes' : 'No' }}
    </div> -->
    <!-- <a href="{{ route('subscriptions.edit', $subscription) }}" class="btn btn-warning">Edit</a> -->
    <a href="{{ route('subscriptions.index') }}" class="btn btn-secondary">Back</a>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const latitude = @json($subscription->alert->latitude ?? null);
    const longitude = @json($subscription->alert->longitude ?? null);

    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latitude}&lon=${longitude}`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        document.getElementById('alertAddress').textContent = data.display_name || 'Address not found';
    } catch (error) {
        console.error('Error during reverse geocoding:', error);
        return 'Error fetching address';
    }

});
</script>