@extends('layouts.dashboard')

@php
    // Ensure $bookedSeats is always defined before any rendering logic
    if (!isset($bookedSeats) || !is_array($bookedSeats)) {
        $bookedSeats = [];
    }
@endphp

<!-- Meta tags -->
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set up CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize Bootstrap tooltips and popovers if used
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Edit booking button click handler
        $(document).on('click', '.editBooking', function(e) {
            e.preventDefault();
            const bookingId = $(this).data('booking-id');
            if (!bookingId) {
                alert('No booking ID found.');
                return;
            }
            // Show loading spinner in modal
            $('#editBookingModal .modal-body').prepend(`
                <div class="text-center loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            // Fetch booking details via AJAX
            $.ajax({
                url: `/bookings/${bookingId}/edit`,
                type: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('.loading-spinner').remove();
                    // Populate modal fields with booking details
                    $('#edit_customer_id').val(response.customer_id || '').trigger('change');
                    $('#edit_bus_id').val(response.bus_id || '');
                    $('#edit_bus_number').val(response.bus_number || '');
                    $('#edit_route_id').val(response.route_id || '');
                    $('#edit_seat').val(response.seat || '');
                    $('#edit_price').val(response.price || '');
                    // Store booking ID for later use
                    $('#editBookingModal').data('booking-id', bookingId);
                    // Show the modal using Bootstrap 5 API
                    var modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
                    modal.show();
                },
                error: function(xhr) {
                    $('.loading-spinner').remove();
                    let msg = 'Failed to load booking details.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });

        // Save edited booking
        $('#saveEditedBooking').click(function() {
            const bookingId = $('#editBookingModal').data('booking-id');
            if (!bookingId) {
                alert('Error: Could not determine which booking to update.');
                return;
            }
            // Show loading state
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            // Get form data

            const data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                customer_id: $('#edit_customer_id').val(),
                bus_id: $('#edit_bus_id').val(),
                bus_number: $('#edit_bus_number').val(),
                route_id: $('#edit_route_id').val(),
                seat: $('#edit_seat').val(),
                price: $('#edit_price').val(),
            };
            // Send update request
            $.ajax({
                url: `/bookings/${bookingId}`,
                type: 'PUT',
                data: data,
                success: function(response) {
                    alert('Booking updated successfully!');
                    window.location.reload();
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html('Save Changes');
                    let errorMsg = 'Failed to update booking.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                    }
                    alert(errorMsg);
                }
            });
        });

        // --- Confirm Booking handler for Add Booking modal ---
        $('#confirmBooking').off('click').on('click', function() {
            // Collect form data
            const customer_id = $('#customer_id').val();
            const bus_id = $('#bus_id').val();
            const bus_number = $('#bus_number').val();
            const route_id = $('#route_id').val();
            const seat = $('#selected_seat').val();
            const price = $('#hidden_price').val();
            const payment_status = $('#payment_status').val();
            // Validate required fields
            if (!customer_id || !bus_id || !route_id || !seat) {
                // Remove alert popup, just highlight button
                $(this).prop('disabled', true)
                       .removeClass('btn-primary')
                       .addClass('btn-danger')
                       .text('Select all fields');
                setTimeout(() => {
                    $(this).prop('disabled', false)
                           .removeClass('btn-danger')
                           .addClass('btn-primary')
                           .text('Confirm Booking');
                }, 2000);
                return;
            }
            // Disable button and show spinner
            const $btn = $(this);
            $btn.prop('disabled', true).removeClass('btn-primary').addClass('btn-danger').html('<span class="spinner-border spinner-border-sm"></span> Booking...');
            // Prepare data
            const data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                customer_id,
                bus_id,
                bus_number,
                route_id,
                seat,
                selected_seat: seat, // Ensure both seat and selected_seat are sent
                price,
            };
           
            $.ajax({
                url: '/bookings',
                type: 'POST',
                data: data,
                success: function(response) {
                    // Remove alert popup, just disable button
                    $btn.prop('disabled', true)
                        .removeClass('btn-primary')
                        .addClass('btn-danger')
                        .html('Booked');
                    $('#seatSelectionModal').modal('hide');
                    window.location.reload();
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).removeClass('btn-danger').addClass('btn-primary').html('Confirm Booking');
                    let msg = 'Failed to create booking.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                    }
                    // Remove alert popup
                }
            });
        });
    });
</script>

<div class="container-fluid px-4 py-3" style="background: #f8f9fa; min-height: 100vh; overflow-x: hidden; width:max-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold" style="margin-left: 250px; color: #222;">Bookings</h2>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#seatSelectionModal" style="min-width: 160px; font-weight: 500;">+ Add Booking</button>
    </div>
    <div class="table-responsive rounded shadow-sm bg-white p-3" style="margin-left: 250px; margin-left: 200px;">
        <table class="table table-bordered table-hover align-middle mb-0" style="min-width: 1200px;">
            <thead class="table-light">
                <tr>
                    <th>Customer ID</th>
                    <th>Customer Name</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Bus Name</th>
                    <th>Bus Number</th>
                    <th>Source</th>
                    <th>Destination</th>
                    <th>Seat</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $booking)
                <tr>
                    <td>{{ $booking->customer->id ?? 'N/A' }}</td>
                    <td>{{ $booking->customer->customer_name ?? 'N/A' }}</td>
                    <td>{{ $booking->customer->customer_address ?? 'N/A' }}</td>
                    <td>{{ $booking->customer->customer_contact ?? 'N/A' }}</td>
                    <td>{{ $booking->bus_name ?? ($booking->route->bus->bus_name ?? 'N/A') }}</td>
                    <td>{{ $booking->bus_number ?? 'N/A' }}</td>
                    <td>{{ $booking->route->source ?? 'N/A' }}</td>
                    <td>{{ $booking->route->destination ?? 'N/A' }}</td>
                    <td>{{ $booking->seat ?? 'N/A' }}</td>
                    <td>{{ $booking->price ?? 'N/A' }}</td> 
                    <td>
                        <div class="d-flex gap-2">
                            <form action="{{ route('bookings.destroy', $booking->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this booking?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            <button type="button" class="btn btn-warning btn-sm editBooking" title="Edit"
                                data-bs-toggle="modal"
                                data-bs-target="#editBookingModal"
                                data-booking-id="{{ $booking->id ?? '' }}"
                                @if(empty($booking->id)) disabled @endif>
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center text-muted">No bookings found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>


<!-- Seat Selection Modal -->
<div class="modal fade" id="seatSelectionModal" tabindex="-1" aria-labelledby="seatSelectionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seatSelectionModalLabel">Select Seats</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bookingForm"
                data-customers='@json($customers)'
                data-routes='@json($routes)'
                data-booked-seats='@json($bookedSeats ?? [])'
                autocomplete="off">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Customer Name</label>
                        <select id="customer_id" name="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" id="address" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" id="contact" class="form-control" readonly placeholder="Customer phone number">
                        <div id="contact-warning" class="text-danger small mt-1" style="display:none;">Contact number</div>
                    </div>
                    <div class="mb-3">
                        <label for="bus_id" class="form-label">Bus Name</label>
                        <select id="bus_id" name="bus_id" class="form-control" required>
                            <option value="">Select Bus</option>
                            @foreach ($routes as $route)
                            <option value="{{ $route->id }}" data-busnum="{{ $route->bus->bus_number ?? '' }}" data-price="{{ $route->price ?? '' }}">
                                {{ $route->bus->bus_name ?? 'Bus' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="bus_id" class="form-label">Bus Number</label>
                        <select id="bus_number" name="bus_number" class="form-control" required>
                            <option value="">Select Bus Number</option>
                               @foreach ($routes as $route)
                            @if (!empty($route->bus))
                            <option value="{{ $route->bus->bus_number ?? 'bus_number' }}">
                                {{ $route->bus->bus_number ?? 'N/A' }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="route_id" class="form-label">Route (Source - Destination)</label>
                        <select id="route_id" name="route_id" class="form-control" required>
                            <option value="">Select Route</option>
                            @foreach ($routes as $route)
                            <option value="{{ $route->id }}" data-source="{{ $route->source ?? '' }}" data-destination="{{ $route->destination ?? '' }}">
                                {{ $route->source ?? '' }} → {{ $route->destination ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="seat" class="form-label">Selected Seat</label>
                        <input type="text" id="seat" class="form-control" readonly>
                      <input type="hidden" name="selected_seat" id="selected_seat">
                    </div>
                    <div id="seat-group-A" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                        <h5 style="width:100%">Group A</h5>
                        @for ($i = 1; $i <= 16; $i++)
                            @php
                                $seatCode = 'A' . $i;
                                $isBooked = is_array($bookedSeats) ? in_array($seatCode, $bookedSeats) : (isset($bookedSeats[$route->id]) && in_array($seatCode, $bookedSeats[$route->id]));
                            @endphp
                            <button type="button" class="btn seat {{ $isBooked ? 'btn-danger' : 'btn-secondary' }}" data-seat="A{{ $i }}" @if($isBooked) disabled style="background-color:#dc3545;border-color:#dc3545;color:white;opacity:0.7;" @endif>A{{ $i }}</button>
                        @endfor
                    </div>
                    <div id="seat-group-B" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                        <h5 style="width:100%">Group B</h5>
                        @for ($i = 1; $i <= 16; $i++)
                            @php
                                $seatCode = 'B' . $i;
                                $isBooked = is_array($bookedSeats) ? in_array($seatCode, $bookedSeats) : (isset($bookedSeats[$route->id]) && in_array($seatCode, $bookedSeats[$route->id]));
                            @endphp
                            <button type="button" class="btn seat {{ $isBooked ? 'btn-danger' : 'btn-secondary' }}" data-seat="B{{ $i }}" @if($isBooked) disabled style="background-color:#dc3545;border-color:#dc3545;color:white;opacity:0.7;" @endif>B{{ $i }}</button>
                        @endfor
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="text" id="price" class="form-control" readonly>
                        <input type="hidden" name="price" id="hidden_price" value="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmBooking">Confirm Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div class="modal fade" id="editBookingModal" tabindex="-1" aria-labelledby="editBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBookingModalLabel">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Alert placeholder for success/error messages -->
                <div id="editModalAlerts"></div>
                <form id="editBookingForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_booking_id">
                    <div class="mb-3">
                        <label for="edit_customer_id" class="form-label">Customer Name</label>
                        <select id="edit_customer_id" name="customer_id" class="form-control" required>
                            <option value="">Select Customer</option>
                            @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->customer_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <input type="text" id="edit_address" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact" class="form-label">Contact Number</label>
                        <input type="text" id="edit_contact" class="form-control" readonly placeholder="Customer phone number">
                        <div id="edit_contact_warning" class="text-danger small mt-1" style="display:none;">Contact number</div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_bus_id" class="form-label">Bus Name</label>
                        <select id="edit_bus_id" name="bus_id" class="form-control" required>
                            <option value="">Select Bus</option>
                            @foreach ($routes as $route)
                            <option value="{{ $route->id }}" data-busnum="{{ $route->bus->bus_number ?? '' }}" data-price="{{ $route->price ?? '' }}">
                                {{ $route->bus->bus_name ?? 'Bus' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_bus_number" class="form-label">Bus Number</label>
                        <select id="edit_bus_number" name="bus_number" class="form-control" required>
                            <option value="">Select Bus Number</option>
                            @foreach ($routes as $route)
                            @if (!empty($route->bus))
                            <option value="{{ $route->bus->bus_number ?? 'bus_number' }}">
                                {{ $route->bus->bus_number ?? 'N/A' }}
                            </option>
                            @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_route_id" class="form-label">Route (Source - Destination)</label>
                        <select id="edit_route_id" name="route_id" class="form-control" required>
                            <option value="">Select Route</option>
                            @foreach ($routes as $route)
                            <option value="{{ $route->id }}" data-source="{{ $route->source ?? '' }}" data-destination="{{ $route->destination ?? '' }}">
                                {{ $route->source ?? '' }} → {{ $route->destination ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_seat" class="form-label">Selected Seat</label>
                        <input type="text" id="edit_seat" class="form-control" readonly>
                        <input type="hidden" id="edit_selected_seat" name="selected_seat" value="">
                    </div>
                    <div id="edit_seat_group_A" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                        <h5 style="width:100%">Group A</h5>
                        @for ($i = 1; $i <= 16; $i++)
                            @php
                                $seatCode = 'A' . $i;
                                $isBooked = is_array($bookedSeats) ? in_array($seatCode, $bookedSeats) : (isset($bookedSeats[$route->id]) && in_array($seatCode, $bookedSeats[$route->id]));
                            @endphp
                            <button type="button" class="btn seat {{ $isBooked ? 'btn-danger' : 'btn-secondary' }}" data-seat="A{{ $i }}" @if($isBooked) disabled style="background-color:#dc3545;border-color:#dc3545;color:white;opacity:0.7;" @endif>A{{ $i }}</button>
                        @endfor
                    </div>
                    <div id="edit_seat_group_B" class="mb-3 d-flex flex-wrap gap-2 align-items-center">
                        <h5 style="width:100%">Group B</h5>
                        @for ($i = 1; $i <= 16; $i++)
                            @php
                                $seatCode = 'B' . $i;
                                $isBooked = is_array($bookedSeats) ? in_array($seatCode, $bookedSeats) : (isset($bookedSeats[$route->id]) && in_array($seatCode, $bookedSeats[$route->id]));
                            @endphp
                            <button type="button" class="btn seat {{ $isBooked ? 'btn-danger' : 'btn-secondary' }}" data-seat="B{{ $i }}" @if($isBooked) disabled style="background-color:#dc3545;border-color:#dc3545;color:white;opacity:0.7;" @endif>B{{ $i }}</button>
                        @endfor
                    </div>
                    <div class="mb-3">
                        <label for="edit_price" class="form-label">Price</label>
                        <input type="text" id="edit_price" class="form-control" readonly>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEditedBooking">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Setup CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // --- Customer change handler for Add Booking modal ---
        $('#customer_id').on('change', function() {
            const selectedId = $(this).val();
            let address = '';
            let contact = '';
            // Get customers data from the data attribute
            const customersData = $('#bookingForm').data('customers');
            if (Array.isArray(customersData)) {
                const customer = customersData.find(c => String(c.id) === String(selectedId));
                if (customer) {
                    address = customer.customer_address || '';
                    contact = customer.customer_contact || customer.contact || customer.phone || '';
                }
            }
            $('#address').val(address);
            $('#contact').val(contact);
        });

        // --- Enable seat selection only after customer, bus, and route are selected ---
        function updateSeatButtonsState() {
            const customerSelected = $('#customer_id').val();
            const busSelected = $('#bus_id').val();
            const routeSelected = $('#route_id').val();
            if (customerSelected && busSelected && routeSelected) {
                $('.seat').prop('disabled', false).each(function() {
                    if ($(this).hasClass('btn-danger')) {
                        $(this).prop('disabled', true); // Booked seats stay disabled
                    }
                });
            } else {
                $('.seat').prop('disabled', true);
            }
        }
        $('#customer_id, #bus_id, #route_id').on('change', updateSeatButtonsState);
        updateSeatButtonsState(); // Initial call

        // When a seat is clicked, set the value in the input and update price
        $(document).on('click', '.seat', function() {
            if (!$(this).prop('disabled')) {
                const seat = $(this).data('seat');
                $('#seat').val(seat);
                $('#selected_seat').val(seat);
                // Update price if available from selected route
                const selectedRouteId = $('#route_id').val();
                const routesData = $('#bookingForm').data('routes');
                if (Array.isArray(routesData)) {
                    const route = routesData.find(r => String(r.id) === String(selectedRouteId));
                    if (route && route.price) {
                        $('#price').val(route.price);
                        $('#hidden_price').val(route.price);
                    }
                }
            }
        });

        // Bootstrap modal instance
        const editModal = new bootstrap.Modal(document.getElementById('editBookingModal'));

        // Edit booking
        $(document).on('click', '.editBooking', function(e) {
            e.preventDefault();
            const bookingId = $(this).data('booking-id');
            if (!bookingId) {
                alert('No booking ID found.');
                return;
            }
            // Show loading spinner in modal
            $('#editBookingModal .modal-body').prepend(`
                <div class="text-center loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            // Fetch booking details via AJAX
            $.ajax({
                url: `/bookings/${bookingId}/edit`,
                type: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $('.loading-spinner').remove();
                    // Populate modal fields with booking details
                    $('#edit_customer_id').val(response.customer_id || '').trigger('change');
                    $('#edit_bus_id').val(response.bus_id || '');
                    $('#edit_bus_number').val(response.bus_number || '');
                    $('#edit_route_id').val(response.route_id || '');
                    $('#edit_seat').val(response.seat || '');
                    $('#edit_price').val(response.price || '');
                    // Store booking ID for later use
                    $('#editBookingModal').data('booking-id', bookingId);
                    // Show the modal using Bootstrap 5 API
                    var modal = new bootstrap.Modal(document.getElementById('editBookingModal'));
                    modal.show();
                },
                error: function(xhr) {
                    $('.loading-spinner').remove();
                    let msg = 'Failed to load booking details.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    alert(msg);
                }
            });
        });

        // Save edited booking
        $('#saveEditedBooking').click(function() {
            const bookingId = $('#editBookingModal').data('booking-id');
            if (!bookingId) {
                alert('Error: Could not determine which booking to update.');
                return;
            }
            // Show loading state
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            // Get form data
            const data = {
                _token: $('meta[name="csrf-token"]').attr('content'),
                customer_id: $('#edit_customer_id').val(),
                bus_id: $('#edit_bus_id').val(),
                bus_number: $('#edit_bus_number').val(),
                route_id: $('#edit_route_id').val(),
                seat: $('#edit_seat').val(),
                price: $('#edit_price').val(),
            };
            // Send update request
            $.ajax({
                url: `/bookings/${bookingId}`,
                type: 'PUT',
                data: data,
                success: function(response) {
                    // Show success message
                    const msg = response.message || 'Booking updated successfully!';
                    alert(msg);
                    
                    // Close modal and refresh page
                    bootstrap.Modal.getInstance(editModal).hide();
                    window.location.reload();
                },
                error: function(xhr) {
                    // Reset button state
                    $btn.prop('disabled', false).html('Save Changes');
                    
                    // Show error message
                    let msg = 'Failed to update booking.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                        }
                    }
                    alert(msg);
                }
            });
        });
        
        // --- Customer change handler for edit modal ---
$('#edit_customer_id').off('change').on('change', function() {
    const selectedId = $(this).val();
    let address = '';
    let contact = '';
    const customer = customersData.find(c => String(c.id) === String(selectedId));
    if (customer) {
        address = customer.customer_address || '';
        contact = customer.customer_contact || customer.contact || customer.phone || '';
    }
    $('#edit_address').val(address);
    $('#edit_contact').val(contact);
});

// In the AJAX success handler for edit modal, after populating #edit_customer_id, trigger change to update address/contact:
$('#edit_customer_id').val(response.customer_id || '').trigger('change');
    });

    // Global AJAX error handler
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('AJAX Error:', error);
        
        if (jqXHR.status === 419) { // CSRF token mismatch
            alert('Your session has expired. The page will refresh to update your session.');
            location.reload();
            return;
        }
        
        if (jqXHR.status === 401) { // Unauthorized
            alert('Please login to continue.');
            window.location.href = '/login';
            return;
        }
        
        if (!jqXHR.responseJSON) {
            alert('An error occurred. Please try again.');
            return;
        }

        const message = jqXHR.responseJSON.message || 'An error occurred. Please try again.';
        alert(message);
    });
</script>