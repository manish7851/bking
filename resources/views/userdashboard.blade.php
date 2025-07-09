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
                                            <tr>
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
</html>



