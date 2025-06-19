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
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-dark text-white vh-100 d-flex flex-column align-items-center py-4">
            <h2 class="mb-4" style="font-size:30px; letter-spacing:1px;">🚍 Bus Booking</h2>
            <div class="profile mb-4 text-center">
                @if(session('customer_id'))
                    @php
                        $customer = \App\Models\Customer::find(session('customer_id'));
                    @endphp
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($customer ? $customer->customer_name : 'User') }}&background=0D8ABC&color=fff&size=80" alt="User" class="rounded-circle mb-2 shadow" width="80" height="80">
                    <p class="mb-0 fw-bold">{{ $customer ? $customer->customer_name : 'User' }}</p>
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
                </li>                <li class="nav-item mb-2">
                    <a href="{{ route('userbookings') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('userbookings') ? 'bg-primary' : '' }}">
                        <i class="fas fa-ticket-alt me-2"></i> My Bookings
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('booking.verify.form') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('booking.verify*') ? 'bg-primary' : '' }}">
                        <i class="fas fa-qrcode me-2"></i> Verify Ticket
                    </a>
                </li>
                <li class="nav-item mt-3">
                    @if(session('customer_id'))
                        <form method="POST" action="/userlogout" style="display:inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">Logout</button>
                        </form>
                    @else
                        <a href="/userlogin" class="btn btn-primary w-100">Login</a>
                    @endif
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
