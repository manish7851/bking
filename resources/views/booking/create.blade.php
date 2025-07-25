@extends('layouts.app')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/seats.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>    .payment-option {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .payment-option:hover {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.05);
    }
    .payment-option.active {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.1);
    }
    .payment-option img {
        height: 25px;
        margin-right: 10px;
    }
    .payment-option input[type="radio"] {
        margin: 0;
    }
    .payment-option label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
    }
    
    .highlight-payment-methods {
        animation: highlight-box 2s ease-in-out;
    }
    
    @keyframes highlight-box {
        0% { box-shadow: none; }
        50% { box-shadow: 0 0 15px rgba(13, 110, 253, 0.5); }
        100% { box-shadow: none; }
    }
    
    .payment-error-modal .swal2-html-container {
        text-align: left;
    }
    
    .payment-error-modal .swal2-html-container ul {
        margin-bottom: 0;
    }
</style>
@endpush

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-4">Book Your Ticket</h3>
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-6">
                    <h5>Route Details</h5>
                    <table class="table">
                        <tr>
                            <th>Bus:</th>
                            <td>{{ $route->bus->bus_name }} ({{ $route->bus->bus_number }})</td>
                        </tr>
                        <tr>
                            <th>From:</th>
                            <td>{{ $route->source }}</td>
                        </tr>
                        <tr>
                            <th>To:</th>
                            <td>{{ $route->destination }}</td>
                        </tr>
                        <tr>
                            <th>Price:</th>
                            <td>Rs. {{ number_format($route->price, 2) }}</td>
                        </tr>                    </table>

                    @if($isLoggedIn && $customer)
                        <h5 class="mt-4">Your Details</h5>
                        <table class="table">
                            <tr>
                                <th>Name:</th>
                                <td>{{ $customer->customer_name }}</td>
                            </tr>
                            <tr>
                                <th>Contact:</th>
                                <td>{{ $customer->customer_contact }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $customer->email }}</td>
                            </tr>
                        </table>
                    @else
                        <div class="alert alert-info mt-4">
                            <h5><i class="fas fa-info-circle me-2"></i>Guest User</h5>
                            <p class="mb-0">You can select a seat and see the booking form, but you'll need to login to complete your booking.</p>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <h5>Select Your Seat</h5>
                    <div class="seat-map mb-4">
                        <div class="bus-layout">                            <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                                <h6 class="w-100 mb-2">Group A</h6>
                                @for ($i = 1; $i <= 16; $i++)
                                    @php
                                        $isBooked = in_array('A'.$i, $booked_seats);
                                        $seatClass = $isBooked ? 'btn-danger' : 'btn-success';
                                        $seatTooltip = $isBooked ? 'This seat is already booked' : 'Available seat - Click to select';
                                    @endphp
                                    <button type="button"
                                        class="btn {{ $seatClass }} seat"
                                        data-seat="A{{ $i }}"
                                        data-bs-toggle="tooltip" 
                                        title="{{ $seatTooltip }}"
                                        {{ $isBooked ? 'disabled' : '' }}>
                                        A{{ $i }}
                                    </button>
                                @endfor
                            </div>
                            <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                                <h6 class="w-100 mb-2">Group B</h6>
                                @for ($i = 1; $i <= 16; $i++)
                                    @php
                                        $isBooked = in_array('B'.$i, $booked_seats);
                                        $seatClass = $isBooked ? 'btn-danger' : 'btn-success';
                                        $seatTooltip = $isBooked ? 'This seat is already booked' : 'Available seat - Click to select';
                                    @endphp
                                    <button type="button"
                                        class="btn {{ $seatClass }} seat"
                                        data-seat="B{{ $i }}"
                                        data-bs-toggle="tooltip" 
                                        title="{{ $seatTooltip }}"
                                        {{ $isBooked ? 'disabled' : '' }}>
                                        B{{ $i }}
                                    </button>
                                @endfor
                            </div>
                        </div>
                        <div class="seat-legend mt-3">
                            <span class="badge bg-success me-2">Available</span>
                            <span class="badge bg-danger me-2">Booked</span>
                            <span class="badge bg-primary">Selected</span>
                        </div>
                    </div>                    <!-- Booking Form -->                    <div id="bookingForm" style="display: none;">
                        <form id="actualBookingForm" method="POST" action="{{ route('userbookings.store') }}" class="mt-4">
                            @csrf
                            <input type="hidden" name="route_id" value="{{ $route->id }}">
                            <input type="hidden" name="customer_id" value="{{ $customer->id ?? '' }}">
                            <input type="hidden" name="bus_id" value="{{ $route->bus->id }}">
                            <input type="hidden" name="bus_number" value="{{ $route->bus->bus_number }}">
                            <input type="hidden" name="bus_name" value="{{ $route->bus->bus_name }}">
                            <input type="hidden" name="customer_name" value="{{ $customer->customer_name ?? '' }}">
                            <input type="hidden" name="customer_email" value="{{ $customer->email ?? '' }}">
                            <input type="hidden" name="customer_contact" value="{{ $customer->customer_contact ?? '' }}">
                            <input type="hidden" name="price" value="{{ $route->price }}">
                            <input type="hidden" name="selected_seat" id="selected_seat" value="" required>
                            <input type="hidden" name="payment_method" id="payment_method" value="esewa">
                            <input type="hidden" name="is_logged_in" value="{{ $isLoggedIn ? '1' : '0' }}">

                            <div class="booking-summary mb-3">
                                <h5>Booking Summary</h5>
                                <p class="mb-1"><strong>Selected Seat:</strong>
                                    <span id="seat_display" class="badge bg-primary">None</span>
                                </p>
                                <p class="mb-1"><strong>Amount to Pay:</strong> Rs. {{ number_format($route->price, 2) }}</p>
                            </div>

                            <!-- Payment Method Selection -->
                            <div class="payment-methods mb-4">
                                <h5>Select Payment Method</h5>
                                <div class="payment-options">
                                    <!-- <div class="payment-option" data-method="esewa">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="payment_method_radio" id="esewa" value="esewa" checked>
                                            <label for="esewa" class="ms-2">
                                                <img src="https://esewa.com.np/common/images/esewa_logo.png" alt="eSewa Logo" class="me-2" style="height: 25px;">Pay with eSewa
                                            </label>
                                        </div>
                                        <small class="text-muted">Redirect to eSewa for secure payment</small>
                                    </div> -->
                                    <div class="payment-option" data-method="khalti">
                                        <div class="d-flex align-items-center">
                                            <input type="radio" name="payment_method_radio" id="khalti" value="khalti">
                                            <label for="khalti" class="ms-2">
                                                <img src="https://khalti.com/static/images/logo/khalti-icon.svg" alt="Khalti Logo" class="me-2" style="height: 25px;">Pay with Khalti
                                            </label>
                                        </div>
                                        <small class="text-muted">Pay instantly using Khalti wallet</small>
                                    </div>
                                </div>
                            </div>                            <div class="mt-4">
                                <button type="submit" id="book-btn" class="btn btn-primary btn-lg">
                                    <span id="btn-text">Book Seat</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<!-- <script src="https://khalti.s3.ap-south-1.amazonaws.com/KPG/dist/2020.12.17.0.0.0/khalti-checkout.iffe.js"></script> -->

<script>    // Set up global variables for payment handlers
    window.khaltiPublicKey = "{{ config('services.khalti.public_key') }}";
    window.khaltiVerifyUrl = "{{ route('payment.khalti.verify') }}";
</script>
 
<script src="{{ asset('js/booking.js') }}"></script>
<script src="{{ asset('js/payment-handler.js') }}"></script>
@if(config('app.debug'))
<script src="{{ asset('js/seat-debug.js') }}"></script>
@endif

<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize payment method selection
    $('.payment-option').click(function() {
        const radio = $(this).find('input[type="radio"]');
        radio.prop('checked', true).trigger('change');
        $('.payment-option').removeClass('active');
        $(this).addClass('active');
    });

    // Update payment method when radio changes
    $('input[name="payment_method_radio"]').change(function() {
        const selectedMethod = $(this).val();
        $('#payment_method').val(selectedMethod);
        const btnText = $('#btn-text');
        btnText.text(selectedMethod === 'esewa' ? 'Pay with eSewa' : 
                    selectedMethod === 'khalti' ? 'Pay with Khalti' : 
                    'Book Seat');
    });

    // Booking form submission
    $('#actualBookingForm').submit(function(e) {
        // Prepare form data for validation
        const formData = $(this).serializeArray().reduce((obj, item) => {
            obj[item.name] = item.value;
            return obj;
        }, {});

        // // Validate prerequisites
        // if (!PaymentHandler.validatePrerequisites(formData)) {
        //     e.preventDefault();
        //     return false;
        // }

        // const paymentMethod = $('#payment_method').val();
        
        // // For eSewa, let the form submit normally
        // if (paymentMethod === 'esewa') {
        //     const $submitBtn = $('#book-btn');
        //     $submitBtn.prop('disabled', true).find('#btn-text').html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        //     return true; // Allow form to submit
        // }
        
        // For Khalti, prevent form submission and handle via Khalti checkout
        if (paymentMethod === 'khalti') {
            e.preventDefault();
            // const amount = parseFloat(formData.price) * 100;
            // khaltiHandler.process(formData, amount, window.khaltiPublicKey).catch(error => {
            //     PaymentHandler.handleError(error, 'gateway');
            // });
            return false;
        }
    });

    // Set default payment method as eSewa
    $('input[name="payment_method_radio"][value="esewa"]').prop('checked', true).trigger('change');
    $('.payment-option[data-method="esewa"]').addClass('active');
});
</script>
@endpush
