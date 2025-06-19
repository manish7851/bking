<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;
use App\Models\Route;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'buses' => Bus::count(),
            'routes' => Route::count(),
            'bookings' => Booking::count(),
            'customers' => Customer::count(),
        ];
        return view('dashboard', compact('stats'));
    }

    /**
     * Show the GPS tracking dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function tracking()
    {
        // Get total route count
        $routeCount = Route::count();
        
        // Get data for active buses
        $activeBuses = Bus::where('tracking_enabled', true)->count();
        
        return view('dashboard.tracking', compact('routeCount', 'activeBuses'));
    }    // Method to handle user dashboard
    public function userDashboard()
    {   
        $customerId = session('customer_id');
        
        // Enhanced logging for debugging
        \Illuminate\Support\Facades\Log::info('DashboardController: userDashboard accessed', [
            'customer_id' => $customerId,
            'has_new_booking_in_session' => session()->has('new_booking'),
            'session_data' => [
                'keys' => array_keys(session()->all()),
                'new_booking_data' => session('new_booking') ? [
                    'id' => session('new_booking')->id ?? 'N/A',
                    'customer_id' => session('new_booking')->customer_id ?? 'N/A'
                ] : null
            ]
        ]);
        
        // If no customer_id in session, try to handle gracefully
        if (!$customerId) {
            \Illuminate\Support\Facades\Log::error('DashboardController: No customer_id in session');
            
            // Try to get from new_booking session if available
            if (session()->has('new_booking')) {
                $newBooking = session('new_booking');
                if ($newBooking && isset($newBooking->customer_id)) {
                    $customerId = $newBooking->customer_id;
                    session(['customer_id' => $customerId]);
                    \Illuminate\Support\Facades\Log::info('DashboardController: Recovered customer_id from new_booking', [
                        'customer_id' => $customerId
                    ]);
                }
            }
            
            // If still no customer_id, redirect to login
            if (!$customerId) {
                return redirect()->route('userlogin')->with('error', 'Please login to view your dashboard');
            }
        }
        
        // Get the most recent booking for the main display
        $recentBooking = session('new_booking') ? [session('new_booking')] : [];

        // Clear the new_booking session after using it
        if (session('new_booking')) {
            session()->forget('new_booking');
        }

        // If there's no recent booking in session, get the latest completed booking
        if (empty($recentBooking)) {
            $latestBooking = Booking::where('customer_id', $customerId)
                ->where('payment_status', 'completed')
                ->orderBy('created_at', 'desc')
                ->first();
            $recentBooking = $latestBooking ? [$latestBooking] : [];
        }
        
        // Get all booking history for this customer
        $allBookings = Booking::where('customer_id', $customerId)
            ->where('payment_status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        
        \Illuminate\Support\Facades\Log::info('DashboardController: Booking data retrieved', [
            'customer_id' => $customerId,
            'recent_booking_count' => count($recentBooking),
            'all_bookings_count' => $allBookings->count(),
            'booking_ids' => $allBookings->pluck('id')->toArray()
        ]);
        
        // Convert to collection for recent booking
        $bookings = collect($recentBooking);
          // Format the booking data for the view
        $formattedBookings = $bookings->map(function ($booking) {
            // Determine payment status display
            $paymentStatus = $booking->payment_status ?? 'unknown';
            $paymentMethod = $booking->payment_method ?? 'N/A';
            
            // Create status display text and badge class
            $statusInfo = $this->getStatusInfo($paymentStatus, $paymentMethod);
            
            // Make sure all necessary fields exist
            return (object) [
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
            ];
        });
        
        return view('userdashboard', [
            'bookings' => $formattedBookings,
            'allBookings' => $allBookings
        ]);
    }    /**
     * Get status information for display
     */
    private function getStatusInfo($paymentStatus, $paymentMethod)
    {
        switch (strtolower($paymentStatus)) {
            case 'completed':
                if (strtolower($paymentMethod) === 'esewa') {
                    return [
                        'text' => 'Paid via eSewa',
                        'badge_class' => 'bg-success payment-status-paid',
                        'icon' => 'fas fa-check-circle'
                    ];
                } elseif (strtolower($paymentMethod) === 'khalti') {
                    return [
                        'text' => 'Paid via Khalti',
                        'badge_class' => 'bg-success payment-status-paid',
                        'icon' => 'fas fa-check-circle'
                    ];
                } else {
                    return [
                        'text' => 'Payment Completed',
                        'badge_class' => 'bg-success payment-status-paid',
                        'icon' => 'fas fa-check-circle'
                    ];
                }
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
