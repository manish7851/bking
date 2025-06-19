@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Booking Confirmed!
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success text-center mb-4">
                        <i class="fas fa-ticket-alt fs-1 mb-3"></i>
                        <h4>Thank you for your booking.</h4>
                        <p class="mb-0">Your seat has been successfully booked. Please find your booking details below.</p>
                    </div>
                    @if(isset($booking))
                    <div class="row">
                        <div class="col-md-7">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>Booking Details
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Booking ID:</th>
                                    <td><strong>#{{ $booking->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $booking->customer->customer_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Seat Number:</th>
                                    <td><span class="badge bg-primary">{{ $booking->seat }}</span></td>
                                </tr>
                                <tr>
                                    <th>Bus:</th>
                                    <td>{{ $booking->bus_name }} ({{ $booking->bus_number }})</td>
                                </tr>
                                <tr>
                                    <th>Route:</th>
                                    <td>{{ $booking->source }} → {{ $booking->destination }}</td>
                                </tr>
                                <tr>
                                    <th>Price:</th>
                                    <td>Rs. {{ number_format($booking->price, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ ucfirst($booking->payment_method) }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ ucfirst($booking->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($booking->status) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Booked At:</th>
                                    <td>
                                        {{ $booking->created_at->isoFormat('YYYY-MM-DD HH:mm') }}
                                        <span class="text-muted small ms-2">
                                            @if($booking->created_at->isSameDay(now()))
                                                (Today)
                                            @elseif($booking->created_at->isSameDay(now()->subDay()))
                                                (Yesterday)
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-5 text-center">
                            <h5 class="mb-3">QR Code</h5>
                            @if($booking->qr_code_path && file_exists(storage_path('app/public/' . $booking->qr_code_path)))
                                <div class="qr-code-container">
                                    <img src="{{ asset('storage/' . $booking->qr_code_path) }}" 
                                         alt="Booking QR Code" 
                                         class="img-fluid border rounded"
                                         style="max-width: 200px;">
                                </div>
                                <p class="text-muted mt-2 small">Scan for verification</p>
                            @else
                                <div class="alert alert-warning">QR code not available.</div>
                            @endif
                        </div>
                    </div>
                    @else
                        <div class="alert alert-danger text-center">
                            Booking details not found.
                        </div>
                    @endif
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Please keep this page or take a screenshot for your reference. You may be asked to show this confirmation during boarding.
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('userdashboard') }}" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.fs-1 {
    font-size: 3rem !important;
}
.qr-code-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 120px;
}
</style>
@endpush
