@extends('layouts.sidebar')
 
<div class="container mt-4">

    <!-- Edit Booking Modal -->
    <div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editBookingModalLabel">Edit Booking</h5>
                    <a href="{{ route('bookings_page') }}" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></a>
                </div>
                <form action="{{ route('bookings.update', $booking->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        < class="mb-3">
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control" required>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $booking->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->customer_name }}</option>
                                @endforeach
                            </select>
</div>
                        
                        <div class="mb-3">
                            <label for="route_id" class="form-label">Route</label>
                            <select name="route_id" id="route_id" class="form-control" required>
                                @foreach($routes as $route)
                                    <option value="{{ $route->id }}" {{ $booking->route_id == $route->id ? 'selected' : '' }}>
                                        {{ $route->source }} → {{ $route->destination }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="bus_id" class="form-label">Bus</label>
                            <select name="bus_id" id="bus_id" class="form-control" required>
                                @foreach($buses as $bus)
                                    <option value="{{ $bus->id }}" {{ $booking->bus_id == $bus->id ? 'selected' : '' }}>{{ $bus->bus_name }} ({{ $bus->bus_number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="seat" class="form-label">Seat</label>
                            <input type="text" name="seat" id="seat" class="form-control" value="{{ $booking->seat }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" name="price" id="price" class="form-control" value="{{ $booking->price }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="Booked" {{ $booking->status == 'Booked' ? 'selected' : '' }}>Booked</option>
                                <option value="Cancelled" {{ $booking->status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="Cancelled" {{ $booking->status == 'Cancelled' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>                        <div class="mb-3">
                            <label class="form-label">Bus Information</label>
                            <div>
                                @php
                                    $selectedBus = $buses->where('id', $booking->bus_id)->first();
                                @endphp
                                @if($selectedBus)
                                    <p><strong>Bus Name:</strong> {{ $selectedBus->bus_name }}</p>
                                    <p><strong>Bus Number:</strong> {{ $selectedBus->bus_number }}</p>
                                @else
                                    <span class="text-muted">No bus selected.</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Update Booking</button>
                        <a href="{{ route('bookings_page') }}" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
 

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var editModal = new bootstrap.Modal(document.getElementById('editBookingModal'));
        editModal.show();
    });
</script>
@endpush

