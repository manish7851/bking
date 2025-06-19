@extends('layouts.app')

 
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <img src="https://esewa.com.np/common/images/esewa-icon.png" alt="eSewa" style="height: 30px; margin-right: 10px;">
                        eSewa Payment
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Booking Details -->
                    <div class="booking-details mb-4">
                        <h6>Booking Details</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Bus:</strong> {{ $booking->bus_name }} ({{ $booking->bus_number }})</p>
                                <p><strong>Route:</strong> {{ $booking->source }} → {{ $booking->destination }}</p>
                                <p><strong>Seat:</strong> {{ $booking->seat }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Passenger:</strong> {{ $booking->customer->customer_name }}</p>
                                <p><strong>Contact:</strong> {{ $booking->contact_number }}</p>
                                <p><strong>Amount:</strong> Rs. {{ number_format($booking->price, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Payment Simulation for Development -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Development Mode:</strong> This is a simulated eSewa payment for testing purposes.
                    </div>

                    <div class="payment-simulation">
                        <h6>Simulate eSewa Payment</h6>                        <form id="simulatePaymentForm" method="POST" action="{{ route('payment.esewa.simulate') }}">
                            @csrf
                            <input type="hidden" name="pid" value="{{ $booking->id }}">
                            <input type="hidden" name="transaction_uuid" value="{{ $transaction_uuid }}">
                            <input type="hidden" name="amount" value="{{ $amount }}">
                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                            <div class="mb-3">
                                <label class="form-label">Simulate Payment Result:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_result" id="success" value="success" checked>
                                    <label class="form-check-label" for="success">
                                        <i class="fas fa-check text-success me-1"></i> Payment Success
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_result" id="failure" value="failure">
                                    <label class="form-check-label" for="failure">
                                        <i class="fas fa-times text-danger me-1"></i> Payment Failure
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <img src="https://esewa.com.np/common/images/esewa-icon.png" alt="eSewa" style="height: 20px; margin-right: 8px;">
                                    Simulate eSewa Payment - Rs. {{ number_format($amount, 2) }}
                                </button>
                                <a href="{{ route('bookings.create', ['route_id' => $booking->route_id]) }}" class="btn btn-outline-secondary">
                                    Cancel Payment
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('simulatePaymentForm').addEventListener('submit', function(e) {
    const paymentResult = document.querySelector('input[name="payment_result"]:checked').value;

    if (paymentResult === 'failure') {
        e.preventDefault();
        // Redirect to failure URL instead
        window.location.href = "{{ route('payment.esewa.failure') }}";
    }
    // For success, let the form submit normally
});
</script>

