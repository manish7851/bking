@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">🎫 Ticket History</h2>
    @if($bookings->isNotEmpty())
        <div class="ticket-history-scroll">
            <table class="table table-bordered align-middle mb-0" style="min-width: 900px;">
                <thead class="table-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>Bus</th>
                        <th>Route</th>
                        <th>Seat</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Booked At</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td>{{ $booking->bus_name }} ({{ $booking->bus_number }})</td>
                            <td>{{ $booking->source }} → {{ $booking->destination }}</td>
                            <td><span class="badge bg-primary">{{ $booking->seat }}</span></td>
                            <td>Rs. {{ number_format($booking->price, 2) }}</td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td>{{ $booking->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('booking.download', ['id' => $booking->id]) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-download"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Move this CSS to your main CSS file for best results -->
        <style>
            .ticket-history-scroll {
                max-height: 400px;
                overflow-y: auto;
                overflow-x: auto;
                width: 100%;
            }
        </style>
    @else
        <div class="alert alert-secondary">No ticket history found.</div>
    @endif
</div>
@endsection

