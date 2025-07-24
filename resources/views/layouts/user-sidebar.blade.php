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
    <!-- Ensure this CSS is loaded after Bootstrap for proper override -->
    <style>
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -250px;
                top: 0;
                width: 250px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s;
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                padding: 1rem 0.5rem;
            }
            .sidebar-backdrop {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0,0,0,0.3);
                z-index: 1049;
            }
        }
        @media (max-width: 575.98px) {
            .main-content {
                padding: 0.5rem 0.2rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <button class="btn btn-primary d-lg-none m-2 position-fixed" style="z-index:1100; top:10px; left:10px;" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="d-flex flex-column flex-lg-row" style="min-height:100vh; overflow-y: hidden;">
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
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ route('userbookings') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('userbookings') ? 'bg-primary' : '' }}">
                        <i class="fas fa-ticket-alt me-2"></i> Ticket Booking
                    </a>
                </li>
                                <li class="nav-item mb-2">
                    <a href="{{ route('subscriptions.index') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('subscriptions.index') ? 'bg-primary' : '' }}">
                        <i class="fas fa-ticket-alt me-2"></i> My Zone Notifications
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
        <div class="sidebar-backdrop d-none" id="sidebarBackdrop"></div>
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
    <script>
        $(function() {
            $('#sidebarToggle').on('click', function() {
                $('.sidebar').toggleClass('show');
                $('#sidebarBackdrop').toggleClass('d-none');
            });
            $('#sidebarBackdrop').on('click', function() {
                $('.sidebar').removeClass('show');
                $(this).addClass('d-none');
            });
            // Hide sidebar on nav click (mobile)
            $('.sidebar .nav-link').on('click', function() {
                if (window.innerWidth < 992) {
                    $('.sidebar').removeClass('show');
                    $('#sidebarBackdrop').addClass('d-none');
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
