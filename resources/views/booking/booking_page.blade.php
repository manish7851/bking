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
                send_ticket_notification: $('#send_ticket_notification').is(':checked') ? 1 : 0
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
                    alert('Booking created successfully!');
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);

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
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
    #mapModal1 {
      display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7); justify-content: center; align-items: center;
      z-index: 9999;
    }
    #mapContainer1 {
      width: 90%; height: 80%; background: #fff; display: flex; flex-direction: column; position: relative;
    }
    #map1 { flex-grow: 1; }
    .map-header1 {
      padding: 8px; background: #f5f5f5; font-size: 14px;
    }
    .map-actions1 {
      padding: 8px; background: #fafafa; text-align: right;
    }
</style>
<style>
    #mapModal2 {
      display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.7); justify-content: center; align-items: center;
      z-index: 9999;
    }
    #mapContainer2 {
      width: 90%; height: 80%; background: #fff; display: flex; flex-direction: column; position: relative;
    }
    #map2 { flex-grow: 1; }
    .map-header2 {
      padding: 8px; background: #f5f5f5; font-size: 14px;
    }
    .map-actions2 {
      padding: 8px; background: #fafafa; text-align: right;
    }
</style>

<div class="container-fluid px-4 py-3" style="background: #f8f9fa; min-height: 100vh; width:max-content; overflow-x:hidden;">
    <div class="d-flex justify-content-between align-items-center mb-6">
        <h2 class="fw-bold" style="margin-left: 300px; color: #222;">Bookings</h2>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#seatSelectionModal" style="min-width: 160px; font-weight: 500;">+ Add Booking</button>
    </div>
<!-- Success Message -->
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
        
    <div class="table-responsive rounded shadow-sm bg-white p-3" style="margin-left: 340px; ">
        <table class="table table-bordered table-hover align-middle mb-0">
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
                <tr id="booking-{{ $booking->id }}">
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
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#pickupDropoffModal" data-booking-id="{{ $booking->id }}">
                            <i class="fas fa-map-pin"></i>
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
                            <button type="button" class="btn seat {{ $isBooked ? 'btn-danger' : 'btn-secondary' }}" data-seat="B{{ $i }}" @if($isBooked) disabled style="background-color:#dc3545;border-color:#dc3545;" @endif>B{{ $i }}</button>
                        @endfor
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="text" id="price" class="form-control" readonly>
                        <input type="hidden" name="price" id="hidden_price" value="">
                    </div>
                    <div class="mb-3">
                        <label>
                            <input type="checkbox" name="send_ticket_notification" id="send_ticket_notification" value="1" checked>
                            Send ticket notification to my email
                        </label>
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

    <!-- Pickup/Dropoff Modal -->
    <div class="modal fade" id="pickupDropoffModal" tabindex="-1" aria-labelledby="pickupDropoffModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="pickupDropoffModalLabel">Pickup & Dropoff Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="pickupDropoffForm" action="{{ route('userbookings.picupdropoff') }}" method="POST">
                @csrf
              <input type="hidden" name="booking_id" id="modal_booking_id">
              <div class="mb-3">
                <label for="pickup_location" class="form-label">Pickup Location</label>
                <input type="text" class="form-control" name="pickup_location" id="pickup_location">
                <span id="selectedCoords1"></span>                
                <input type="hidden"  class="form-control" name="pickup_location_latitude" id="pickup_location_latitude" required>
                <input type="hidden"  class="form-control" name="pickup_location_longitude" id="pickup_location_longitude" required>
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="openMap(1)">Pick on Map</button>
              </div>
              <div class="mb-3">
                <label for="pickup_remark" class="form-label">Pickup Remark</label>
                <input type="text" class="form-control" name="pickup_remark" id="pickup_remark">
                </div>
              <div class="mb-3">
                <label for="dropoff_location" class="form-label">Dropoff Location</label>
                <input type="text" class="form-control" name="dropoff_location" id="drop_off_location">
                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" onclick="openMap(2)">Pick on Map</button>
                <span id="selectedCoords2"></span>
                <input type="hidden" class="form-control" name="dropoff_location_latitude" id="drop_off_location_latitude" required>
                <input type="hidden" class="form-control" name="dropoff_location_longitude" id="drop_off_location_longitude" required>
              </div>
              <div class="mb-3">
                <label for="dropoff_remark" class="form-label">Dropoff Remark</label>
                <input type="text" class="form-control" name="dropoff_remark" id="dropoff_remark">
              </div>
              <button type="submit" class="btn btn-success">Save</button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div id="mapModal1">
    <div id="mapContainer1">
        <div class="map-header1">
        <strong>Address:</strong> <span id="selectedAddress1">Click on the map</span><br>
        <strong>Coordinates:</strong> <span id="selectedCoords1">-</span>
        </div>
        <div id="map1"></div>
        <div class="map-actions1">
            <button type="button" onclick="confirmLocation(1)">Pick on Map</button>
            <button type="button" onclick="closeMap(1)">Close</button>
        </div>
    </div>
    </div>
    
    <div id="mapModal2">
    <div id="mapContainer2">
        <div class="map-header1">
        <strong>Address:</strong> <span id="selectedAddress2">Click on the map</span><br>
        <strong>Coordinates:</strong> <span id="selectedCoords2">-</span>
        </div>
        <div id="map2"></div>
        <div class="map-actions2">
            <button type="button" onclick="confirmLocation(2)">Pick on Map</button>
            <button type="button" onclick="closeMap(2)">Close</button>
        </div>
    </div>
    </div>
    <!-- External JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
            const routeId = $('#route_id').val();

            if (routeId) {
                // Fetch booked seats for the selected route
                $.ajax({
                    url: `/bookings/booked_seats/${routeId}`,
                    type: 'GET',
                    success: function(bookedSeats) {
                        // Reset all seats to default state
                        $('.seat').removeClass('btn-danger').addClass('btn-secondary').prop('disabled', false);

                        // Mark booked seats as disabled and red
                        bookedSeats.forEach(function(seat) {
                            $(`.seat[data-seat="${seat}"]`).removeClass('btn-secondary').addClass('btn-danger').prop('disabled', true);
                        });
                    },
                    error: function() {
                        console.error('Could not fetch booked seats.');
                        // Disable all seats if there's an error
                        $('.seat').prop('disabled', true);
                    }
                });
            } else {
                // If no route is selected, disable all seats
                $('.seat').prop('disabled', true);
            }
        }
        $('#customer_id, #bus_id, #route_id, #travel_date').on('change', updateSeatButtonsState);
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
<script>
window.serverData = {
  bookings: @json($bookings),  
};


    var pickupDropoffModal = document.getElementById('pickupDropoffModal');
      pickupDropoffModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var bookingId = button.getAttribute('data-booking-id');
        document.getElementById('modal_booking_id').value = bookingId;
        const currentBooking = window.serverData.bookings.find(b => b.id == bookingId);
        // console.log(bookingId, currentBooking);
        if(currentBooking?.pickup_location != null && currentBooking?.pickup_location != '') {
            const [pickupLat, pickupLng] = currentBooking.pickup_location.split(',').map(Number);
            window.pickupLat = pickupLat || 27.7;
            window.pickupLng = pickupLng || 85.3;
            updateAddress({ lat: window.pickupLat, lng: window.pickupLng }, 1);

        } else {
            document.getElementById(`selectedCoords1`).textContent = ``;
            document.getElementById(`selectedAddress1`).textContent = '';
            document.getElementById("pickup_location").value = '';
        }
    
        if(currentBooking?.dropoff_location != null && currentBooking?.dropoff_location != '') {

            // document.getElementById('pickup_location_latitude').value = pickupLat || '';
            // document.getElementById('pickup_location_longitude').value = pickupLng || '';
            const [dropoffLat, dropoffLng] = currentBooking.dropoff_location.split(',').map(Number);
            // document.getElementById('dropoff_location_latitude').value = dropoffLat || '';
            // document.getElementById('dropoff_location_longitude').value = dropoffLng || '';
            window.dropoffLat = dropoffLat || 27.7;
            window.dropoffLng = dropoffLng || 85.3;
            // console.log(`Dropoff: ${dropoffLat}, ${dropoffLng}`);
            updateAddress({ lat: window.dropoffLat, lng: window.dropoffLng }, 2);
        } else {
            document.getElementById(`selectedCoords2`).textContent = ``;
            document.getElementById(`selectedAddress2`).textContent = '';
            document.getElementById("drop_off_location").value = '';
        }
        document.getElementById('pickup_remark').value = currentBooking.pickup_remark ||'';
        document.getElementById('dropoff_remark').value = currentBooking.dropoff_remark ||'';

      });
      
    let map1, marker1, selectedLatLng1 = null, selectedAddr1 = '';
    let map2, marker2, selectedLatLng2 = null, selectedAddr2 = '';

    function openMap(number) {        
            document.getElementById(`mapModal${number}`).style.display = 'flex';

    let lat1 = window.pickupLat;//parseFloat(document.getElementById(`pickup_location_latitude`).value);
    let lng1 = window.pickupLng;//parseFloat(document.getElementById(`pickup_location_longitude`).value);
    let lat2 = window.dropoffLat; //(document.getElementById(`drop_off_location_latitude`).value);
    let lng2 = window.dropoffLng;//parseFloat(document.getElementById(`drop_off_location_longitude`).value);

    // If no coordinates provided, use default location
    if (isNaN(lat1) || isNaN(lng1)) {
        lat1 = 27.7; lng1 = 85.3;
    }
    if(isNaN(lat2) || isNaN(lng2)) {
        lat2 = 27.7; lng2 = 85.3;
    }

    if (number == 1 ? !map1: !map2) {
        if(number == 1 && !map1) {
            map1 = L.map(`map${number}`).setView([lat1, lng1], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map1);
            marker1 = L.marker([lat1, lng1], { draggable: true }).addTo(map1);
            marker1.on('dragend', () => updateAddress(marker1.getLatLng(), 1));
            map1.on('click', e => {
                marker1.setLatLng(e.latlng);
                updateAddress(e.latlng, 1);
            });
        } else if(!map2){
            map2 = L.map(`map${number}`).setView([lat2, lng2], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map2);
            marker2 = L.marker([lat2, lng2], { draggable: true }).addTo(map2);
            marker2.on('dragend', () => updateAddress(marker2.getLatLng(), 2));
            map2.on('click', e => {
                marker2.setLatLng(e.latlng);
                updateAddress(e.latlng, 2);
            });
        }        
    } else if(number == 1) {
        map1.setView([lat1, lng1], 13);
        marker1.setLatLng([lat1, lng1]);
        updateAddress({ lat:lat1, lng: lng1 }, 1);

    } else {
        map2.setView([lat2, lng2], 13);
        marker2.setLatLng([lat2, lng2]);
        updateAddress({ lat:lat2, lng: lng2 }, 2);
    }
    }

    function updateAddress(latlng, number) {
        if(number == 1) {
            selectedLatLng1 = latlng;
        } else if(number == 2) {
            selectedLatLng2 = latlng;
        }
        document.getElementById(`selectedCoords${number}`).textContent = `${latlng.lat.toFixed(6)}, ${latlng.lng.toFixed(6)}`;

    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${latlng.lat}&lon=${latlng.lng}`)
        .then(res => res.json())
        .then(data => {
        selectedAddr = data.display_name || 'Unknown location';
        document.getElementById(`selectedAddress${number}`).textContent = selectedAddr;
        const element = number == 1 ? document.getElementById("pickup_location") : document.getElementById("drop_off_location");
        console.log(element);
        element.value= selectedAddr;
        console.log(number == 1? selectedAddr1 : selectedAddr2);
        })
        .catch(() => {
        selectedAddr = 'Unknown location';
        document.getElementById(`selectedAddress${number}`).textContent = selectedAddr;
        });
    }

    function confirmLocation(number) {
        if(number == 1) {
            document.getElementById(`pickup_location_latitude`).value = selectedLatLng1.lat.toFixed(6);
            document.getElementById(`pickup_location_longitude`).value = selectedLatLng1.lng.toFixed(6);
        } else if(number == 2) {
            document.getElementById(`drop_off_location_latitude`).value = selectedLatLng2.lat.toFixed(6);
            document.getElementById(`drop_off_location_longitude`).value = selectedLatLng2.lng.toFixed(6);
        }
        closeMap(number);
    }

    function closeMap(number) {
    document.getElementById(`mapModal${number}`).style.display = 'none';
    }

    
</script>
