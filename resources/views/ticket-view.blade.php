@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center">
                    <h3 class="mb-0">Bus Ticket</h3>
                    <p class="mb-0">GHORAHI YATAYAT</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="mb-3">Ticket Details</h4>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="35%">Booking ID:</th>
                                    <td><strong>#{{ $booking->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Passenger Name:</th>
                                    <td>{{ $booking->customer_name }}</td>
                                </tr>
                                <tr>
                                    <th>Bus Name:</th>
                                    <td>{{ $booking->bus_name }}</td>
                                </tr>
                                <tr>
                                    <th>Bus Number:</th>
                                    <td>{{ $booking->bus_number }}</td>
                                </tr>
                                <tr>
                                    <th>Route:</th>
                                    <td>{{ $booking->source }} → {{ $booking->destination }}</td>
                                </tr>
                                <tr>
                                    <th>Seat Number:</th>
                                    <td><span class="badge bg-primary fs-6">{{ $booking->seat ?? 'N/A' }}</span></td>
                                </tr>
                                <tr>
                                    <th>Price:</th>
                                    <td><strong>Rs. {{ number_format($booking->price, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Booking Date:</th>
                                    <td>{{ $booking->created_at->format('M d, Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $booking->payment_status === 'completed' ? 'success' : ($booking->payment_status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($booking->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>{{ ucfirst($booking->payment_method) }}</td>
                                </tr>
                                @if($booking->verification_code)
                                <tr>
                                    <th>Verification Code:</th>
                                    <td><code>{{ $booking->verification_code }}</code></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-4 text-center">
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
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    QR Code not available
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="generateQRCode()">
                                    Generate QR Code
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <div class="btn-group" role="group">
                        <a href="{{ route('downloadTicket', $booking->id) }}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="printTicket()">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                        <a href="{{ route('userbookings') }}" class="btn btn-outline-dark">
                            <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Print-only CSS -->
<style media="print">
    .btn, .card-footer, .btn-group {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 2px solid #000 !important;
    }
    .card-header {
        background-color: #000 !important;
        color: #fff !important;
    }
    @page {
        margin: 0.5in;
    }
    body {
        font-size: 12pt;
    }
</style>

<script>
function printTicket() {
    window.print();
}

function generateQRCode() {
    fetch(`/api/bookings/{{ $booking->id }}/qr-code`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to generate QR code: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error generating QR code');
    });
}
</script>
@endsection
