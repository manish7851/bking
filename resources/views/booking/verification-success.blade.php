@extends('layouts.app')

 
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>Ticket Verified Successfully!
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-success text-center mb-4">
                        <i class="fas fa-shield-check fs-1 mb-3"></i>
                        <h4>Valid Ticket</h4>
                        <p class="mb-0">This ticket has been successfully verified and is valid for travel.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-ticket-alt me-2"></i>Booking Details
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Booking ID:</th>
                                    <td><strong>#{{ $booking->id }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Customer:</th>
                                    <td>{{ $verification_details['customer_name'] }}</td>
                                </tr>
                                <tr>
                                    <th>Seat Number:</th>
                                    <td><span class="badge bg-primary">{{ $verification_details['seat'] }}</span></td>
                                </tr>
                                <tr>
                                    <th>Bus:</th>
                                    <td>{{ $verification_details['bus'] }}</td>
                                </tr>
                                <tr>
                                    <th>Route:</th>
                                    <td>{{ $verification_details['route'] }}</td>
                                </tr>
                                <tr>
                                    <th>Price:</th>
                                    <td>Rs. {{ number_format($verification_details['price'], 2) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">
                                <i class="fas fa-info-circle me-2"></i>Verification Details
                            </h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Verified At:</th>
                                    <td>{{ $verification_details['verified_at'] }}</td>
                                </tr>
                                <tr>
                                    <th>Booking Date:</th>
                                    <td>{{ $verification_details['booking_date'] }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Status:</th>
                                    <td>
                                        <span class="badge bg-success">
                                            {{ ucfirst($verification_details['payment_status']) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-info">Valid</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> This verification confirms that the ticket is genuine and the passenger is authorized to travel on this bus.
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('booking.verify.form') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Verify Another Ticket
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.fs-1 {
    font-size: 3rem !important;
}
</style>
 