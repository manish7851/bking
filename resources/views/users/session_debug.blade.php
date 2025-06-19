<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<div class="sidebar bg-dark text-white vh-100 d-flex flex-column align-items-center py-4" style="max-width:fit-content;">
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
        <li class="nav-item mb-2">            <a href="{{ route('profile.show') }}" class="nav-link text-white px-3 py-2 rounded {{ request()->routeIs('profile.*') ? 'bg-primary' : '' }}">
                <i class="fas fa-user me-2"></i> My Profile
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="/userbookings" class="nav-link text-white px-3 py-2 rounded {{ request()->is('userbookings') ? 'bg-primary' : '' }}">
                <i class="fas fa-ticket-alt me-2"></i> Bookings
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

