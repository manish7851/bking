<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Bus Booking') }}</title>
    
    <!-- Fonts and Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/user-sidebar.css') }}" rel="stylesheet">
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

    @stack('styles')
</head>
<body style="height: 100vh; overflow: hidden;">
    <div class="d-flex" style="height: 100vh;">
        <!-- Sidebar -->
        <div class="sidebar bg-dark text-white d-flex flex-column align-items-center py-4" style="height: 100vh; min-width: 220px;">
            <h2 class="mb-4" style="font-size:30px; letter-spacing:1px;">🚍 Bus Booking</h2>
            <div class="profile mb-4 text-center">
                @if(session('customer_id'))
                    @php
                        $customer = \App\Models\Customer::find(session('customer_id'));
                    @endphp
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($customer ? $customer->customer_name : 'User') }}&background=0D8ABC&color=fff&size=80" alt="User" class="rounded-circle mb-2 shadow" width="80" height="80">
                    <p class="mb-0 fw-bold">{{ $customer ? $customer->customer_name : 'User' }}</p>
                @else
                    <img src="https://ui-avatars.com/api/?name=Guest&background=888&color=fff&size=80" alt="Guest" class="rounded-circle mb-2 shadow" width="80" height="80">
                    <p class="mb-0 fw-bold">Guest</p>
                @endif
            </div>
            <ul class="nav flex-column w-100">
                <li class="nav-item mb-2">
                    <a href="/userdashboard" class="nav-link text-white px-3 py-2 rounded {{ request()->is('userdashboard') ? 'bg-primary' : '' }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('profile.show') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('profile.*') ? 'bg-primary' : '' }}">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('userbookings') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('userbookings') ? 'bg-primary' : '' }}">
                        <i class="fas fa-ticket-alt me-2"></i> My Bookings
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('subscriptions.index') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('subscriptions.index') ? 'bg-primary' : '' }}">
                        <i class="fas fa-ticket-alt me-2"></i> My Zone Notifications
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-1 d-4" style="overflow-y: auto; height: 100vh;">
            <div class="container-fluid py-4">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10">
                                    <!-- Success Message -->
                        @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif
                        @if($bookings->isNotEmpty())
                            @php $booking = $bookings->first(); @endphp
                            <div class="card mb-4 shadow-sm" style="width: fit-content;">
                                <div class="card-body">
                                          <h2 class="mb-4">🎫 Your Latest Ticket</h2>
                                    <h5 class="card-title">Bus: {{ $booking->bus_name }} ({{ $booking->bus_number }})</h5>
                                    <p class="mb-1"><strong>Route:</strong> {{ $booking->source }} → {{ $booking->destination }}</p>
                                    <p class="mb-1"><strong>Seat:</strong> <span class="badge bg-primary">{{ $booking->seat }}</span></p>
                                    <p class="mb-1"><strong>Price:</strong> Rs. {{ number_format($booking->price, 2) }}</p>
                                    <p class="mb-1"><strong>Payment Status:</strong> <span class="badge {{ $booking->status_badge_class }}"><i class="{{ $booking->status_icon }} me-1"></i>{{ $booking->status }}</span></p>
                                    <p class="mb-1"><strong>Booked At:</strong> {{ $booking->created_at->format('d M Y, h:i A') }}</p>
                                    <a href="{{ route('booking.download', ['id' => $booking->id]) }}" class="btn btn-outline-success mt-2">
                                        <i class="fas fa-download me-1"></i>Download Ticket
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">No recent bookings found.</div>
                        @endif

                        <h3 class="mb-3 mt-5">📜 Ticket History</h3>
                        @if($allBookings->isNotEmpty())
                        <div style="margin-top: 20px;">
                            <div class="ticket-history-scroll table-responsive" style="max-height: 400px; overflow-y: auto; width: fit-content;">
                                <table class="table table-bordered align-middle mb-0 w-100">
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
                                            <th>Track Bus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        </div>
                                        @foreach($allBookings as $b)
                                            <tr id="booking-{{ $b->id }}">
                                                <td>{{ $b->id }}</td>
                                                <td>{{ $b->bus_name }} ({{ $b->bus_number }})</td>
                                                <td>{{ $b->source }} → {{ $b->destination }}</td>
                                                <td><span class="badge bg-primary">{{ $b->seat }}</span></td>
                                                <td>Rs. {{ number_format($b->price, 2) }}</td>
                                                <td><span class="badge bg-success">Paid</span></td>
                                                <td>{{ $b->created_at->format('d M Y, h:i A') }}</td>
                                                <td>
                                                    <a href="{{ route('booking.download', ['id' => $b->id]) }}" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </td>
                                                <td>
                                                    @php
                                                        $activeRoute = $b->bus->routes()
                                                        ->whereDate('trip_date', '>=', now()->format('Y-m-d'))
                                                        ->orderBy('trip_date')
                                                        ->first();
                                                    @endphp
                                                    @if($activeRoute && $activeRoute->id === $b->route_id)
                                                        <a href="{{ route('booking.trackBusActiveRoute', ['bus_id' => $b->bus->id, 'active_route_id' => $b->route->id]) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-map-marker-alt"></i> Track Bus
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $activeRoute = $b->bus->routes()
                                                        ->whereDate('trip_date', '>=', now()->format('Y-m-d'))
                                                        ->orderBy('trip_date')
                                                        ->first();
                                                    @endphp
                                                    @if($activeRoute && $activeRoute->id === $b->route_id)
                                                        <a href="{{ route('subscriptions.create', ['active_route_id' => $b->route->id]) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-map-marker-alt"></i> Create Zone Notification
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#pickupDropoffModal" data-booking-id="{{ $b->id }}">
                                                        <i class="fas fa-map-pin"></i> Pickup/Dropoff
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>    @else
                            <div class="alert alert-secondary">No ticket history found.</div>
                        @endif
                    </div>
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

    <!-- Bootstrap and other Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')

    <style>
        .ticket-history-scroll {
            max-height: 400px;
            overflow-y: v;
            width: 100%;
        }
        @media (max-width: 991.98px) {
            .ticket-history-scroll {
                min-width: 100%;
                padding-bottom: 1rem;
            }
            .ticket-history-scroll table {
                font-size: 0.95em;
            }
        }
    </style>
</body>
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
        console.log(bookingId, currentBooking);
            const [pickupLat, pickupLng] = currentBooking.pickup_location.split(',').map(Number);
            // document.getElementById('pickup_location_latitude').value = pickupLat || '';
            // document.getElementById('pickup_location_longitude').value = pickupLng || '';
            const [dropoffLat, dropoffLng] = currentBooking.dropoff_location.split(',').map(Number);
            // document.getElementById('dropoff_location_latitude').value = dropoffLat || '';
            // document.getElementById('dropoff_location_longitude').value = dropoffLng || '';
            window.pickupLat = pickupLat || 27.7;
            window.pickupLng = pickupLng || 85.3;
            window.dropoffLat = dropoffLat || 27.7;
            window.dropoffLng = dropoffLng || 85.3;
            console.log(`Pickup: ${pickupLat}, ${pickupLng}`);
            console.log(`Dropoff: ${dropoffLat}, ${dropoffLng}`);
            updateAddress({ lat: window.pickupLat, lng: window.pickupLng }, 1);
            updateAddress({ lat: window.dropoffLat, lng: window.dropoffLng }, 2);
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


