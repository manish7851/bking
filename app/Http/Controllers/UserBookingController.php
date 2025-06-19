<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\Booking;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserBookingController extends Controller
{    public function search(Request $request)
    {
        // Check if user is authenticated
        if (!session('customer_id')) {
            return redirect()->route('userlogin')->with('error', 'Please login to search for bookings.');
        }
        
        try {
            // Build the route search query
            $query = Route::with('bus');

            if ($request->filled('source')) {
                $query->where('source', 'LIKE', '%' . $request->input('source') . '%');
            }

            if ($request->filled('destination')) {
                $query->where('destination', 'LIKE', '%' . $request->input('destination') . '%');
            }

            // Note: Date filtering on routes doesn't make much sense for bus routes
            // Routes are generally always available. The date filter might be better applied
            // to booking dates, but for now we'll keep it for consistency with the original code
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->input('date'));
            }

            $routes = $query->get();

            // Also get user's existing bookings if they're logged in
            $bookings = collect();
            if (session('customer_id')) {
                $bookings = Booking::where('customer_id', session('customer_id'))
                    ->where('payment_status', 'completed')
                    ->orderBy('created_at', 'desc')
                    ->with(['route.bus'])
                    ->get();
            }

            Log::info('User booking search performed', [
                'search_params' => $request->only(['source', 'destination', 'date']),
                'routes_found' => $routes->count(),
                'customer_id' => session('customer_id'),
                'existing_bookings' => $bookings->count()
            ]);

            return view('users.userbookings', [
                'routes' => $routes,
                'bookings' => $bookings
            ]);
        } catch (\Exception $e) {
            Log::error('User booking search error: ' . $e->getMessage());
            return back()->with('error', 'Unable to search routes. Please try again.');
        }
    }
}
