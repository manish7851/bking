<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Booking;
use App\Models\Customer;

class DashboardController extends Controller
{
    // Admin dashboard stats
    public function index(Request $request)
    {
        $stats = [
            'buses' => Bus::count(),
            'routes' => Route::count(),
            'bookings' => Booking::count(),
            'customers' => Customer::count(),
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'stats' => $stats]);
        }

        return view('dashboard', compact('stats'));
    }

    // GPS tracking dashboard
    public function tracking(Request $request)
    {
        $routeCount = Route::count();
        $activeBuses = Bus::where('tracking_enabled', true)->count();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'route_count' => $routeCount,
                'active_buses' => $activeBuses
            ]);
        }

        return view('dashboard.tracking', compact('routeCount', 'activeBuses'));
    }

    // User dashboard
    public function userDashboard(Request $request)
    {
        $customerId = session('customer_id');

        if (!$customerId) {
            if (session()->has('new_booking')) {
                $newBooking = session('new_booking');
                $customerId = $newBooking->customer_id ?? null;
                session(['customer_id' => $customerId]);
            }

            if (!$customerId) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please login to view dashboard'], 401);
                }
                return redirect()->route('userlogin')->with('error', 'Please login to view your dashboard');
            }
        }

        $recentBooking = session('new_booking') ? [session('new_booking')] : [];
        if (session('new_booking')) session()->forget('new_booking');

        if (empty($recentBooking)) {
            $latestBooking = Booking::where('customer_id', $customerId)
                ->where('payment_status', 'completed')
                ->orderBy('created_at', 'desc')
                ->first();
            $recentBooking = $latestBooking ? [$latestBooking] : [];
        }

        $allBookings = Booking::where('customer_id', $customerId)
            ->where('payment_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $bookings = collect($recentBooking)->map(function ($booking) {
            $paymentStatus = $booking->payment_status ?? 'unknown';
            $paymentMethod = $booking->payment_method ?? 'N/A';
            $statusInfo = $this->getStatusInfo($paymentStatus, $paymentMethod);

            return (object)[
                'id' => $booking->id,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'bus_name' => $booking->bus_name ?? 'N/A',
                'bus_number' => $booking->bus_number ?? 'N/A',
                'source' => $booking->source ?? 'N/A',
                'destination' => $booking->destination ?? 'N/A',
                'seat' => $booking->seat ?? $booking->selected_seat ?? 'N/A',
                'price' => $booking->price ?? 0,
                'status' => $statusInfo['text'],
                'status_badge_class' => $statusInfo['badge_class'],
                'status_icon' => $statusInfo['icon'],
                'payment_details' => $booking->payment_details ?? null,
                'created_at' => $booking->created_at ?? now(),
                'pickup_location' => $booking->pickup_location ?? '',
                'pickup_remark' => $booking->pickup_remark ?? '',
                'dropoff_location' => $booking->dropoff_location ?? '',
                'dropoff_remark' => $booking->dropoff_remark ?? ''
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'recent_bookings' => $bookings,
                'all_bookings' => $allBookings
            ]);
        }

        return view('userdashboard', [
            'bookings' => $bookings,
            'allBookings' => $allBookings
        ]);
    }

    // Get status information for display
    private function getStatusInfo($paymentStatus, $paymentMethod)
    {
        switch (strtolower($paymentStatus)) {
            case 'completed':
                $text = match (strtolower($paymentMethod)) {
                    'esewa' => 'Paid via eSewa',
                    'khalti' => 'Paid via Khalti',
                    default => 'Payment Completed',
                };
                return [
                    'text' => $text,
                    'badge_class' => 'bg-success payment-status-paid',
                    'icon' => 'fas fa-check-circle'
                ];
            case 'pending':
                return [
                    'text' => 'Payment Pending',
                    'badge_class' => 'bg-warning payment-status-pending',
                    'icon' => 'fas fa-clock'
                ];
            case 'failed':
                return [
                    'text' => 'Payment Failed',
                    'badge_class' => 'bg-danger payment-status-failed',
                    'icon' => 'fas fa-times-circle'
                ];
            case 'cancelled':
                return [
                    'text' => 'Booking Cancelled',
                    'badge_class' => 'bg-secondary',
                    'icon' => 'fas fa-ban'
                ];
            default:
                return [
                    'text' => 'Unknown Status',
                    'badge_class' => 'bg-secondary',
                    'icon' => 'fas fa-question-circle'
                ];
        }
    }
}
