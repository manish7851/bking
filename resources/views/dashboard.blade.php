@extends('layouts.dashboard')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    .stat-card .card-body {
        padding: 1.5rem;
    }
    .stat-card .card-title {
        color: #6c757d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .stat-card .stat-value {
        font-size: 2rem;
        font-weight: 600;
        color: #2c3e50;
        margin: 10px 0;
    }
    .stat-card .stat-icon {
        font-size: 2.5rem;
        opacity: 0.3;
        position: absolute;
        right: 1rem;
        bottom: 1rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h2>Dashboard Overview</h2>

    <div class="row g-4 my-4">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('buses.index') }}" style="text-decoration:none; color:inherit;">
                <div class="card stat-card bg-white border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Buses</h6>
                        <div class="stat-value">{{ $stats['buses'] ?? 0 }}</div>
                        <i class="fas fa-bus stat-icon"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('routes.index') }}" style="text-decoration:none; color:inherit;">
                <div class="card stat-card bg-white border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Routes</h6>
                        <div class="stat-value">{{ $stats['routes'] ?? 0 }}</div>
                        <i class="fas fa-road stat-icon"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('bookings_page') }}" style="text-decoration:none; color:inherit;">
                <div class="card stat-card bg-white border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Bookings</h6>
                        <div class="stat-value">{{ $stats['bookings'] ?? 0 }}</div>
                        <i class="fas fa-ticket-alt stat-icon"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ url('/customers') }}" style="text-decoration:none; color:inherit;">
                <div class="card stat-card bg-white border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Total Customers</h6>
                        <div class="stat-value">{{ $stats['customers'] ?? 0 }}</div>
                        <i class="fas fa-users stat-icon"></i>
                    </div>
                </div>
            </a>
        </div>    
    </div>

    @if (session('bookings'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Your Booking History</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Bus Name</th>
                                        <th>Seat</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (session('bookings') as $booking)
                                        <tr>
                                            <td>{{ $booking->id }}</td>
                                            <td>{{ $booking->bus_name }}</td>
                                            <td>{{ $booking->seat }}</td>
                                            <td>{{ $booking->status }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        Swal.fire({
            title: 'Success!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonText: 'OK'
        });
    @endif
});
</script>
@endpush