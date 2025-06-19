@php
    use Illuminate\Support\Facades\Auth;
    $isAdmin = Auth::check();
    $isCustomer = Auth::guard('customer')->check();
    $user = $isAdmin ? Auth::user() : ($isCustomer ? Auth::guard('customer')->user() : null);
@endphp
<div class="sidebar">
    <h2><i class="fas fa-bus"></i> Bus Booking</h2>    <div class="profile">
        @if($isAdmin)
            <img src="https://ui-avatars.com/api/?name={{ $user->name }}" alt="Admin">
            <p>{{ $user->name }} (Admin)</p>
        @elseif($isCustomer)
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->customer_name) }}&background=0D8ABC&color=fff" alt="Customer">
            <p>{{ $user->customer_name }}</p>
        @else
            <img src="https://ui-avatars.com/api/?name=Guest" alt="Guest">
            <p>Guest</p>
        @endif
    </div>
    
    <ul>
        <li><a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="/dashboard/tracking"><i class="fas fa-satellite-dish"></i> GPS Tracking</a></li>
        <li><a href="/buses"><i class="fas fa-bus"></i> Buses</a></li>
        <li><a href="/map/buses"><i class="fas fa-map-marked-alt"></i> Bus Map</a></li>
        <li><a href="/routes"><i class="fas fa-route"></i> Routes</a></li>
        <li><a href="/customers"><i class="fas fa-users"></i> Customers</a></li>
        <li><a href="{{ route('bookings_page') }}"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
        @if(Auth::check())
            <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        @else
            <li><a href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a></li>
        @endif
    </ul>
</div>
