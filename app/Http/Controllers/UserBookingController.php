<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;

class UserBookingController extends Controller
{
    public function search(Request $request)
    {
        // Check if user is authenticated
        if (!session('customer_id')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to search for bookings.'
                ], 401);
            }
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

            if ($request->filled('date')) {
                $query->whereDate('trip_date', '=', trim($request->date));
            } else {
                $query->whereDate('trip_date', '>=', now()->toDateString());
            }

            $routes = $query->get();

            // User's existing bookings
            $bookings = Booking::where('customer_id', session('customer_id'))
                ->where('payment_status', 'completed')
                ->orderBy('created_at', 'desc')
                ->with(['route.bus'])
                ->get();

            Log::info('User booking search performed', [
                'search_params' => $request->only(['source', 'destination', 'date']),
                'routes_found' => $routes->count(),
                'customer_id' => session('customer_id'),
                'existing_bookings' => $bookings->count()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'routes' => $routes,
                    'bookings' => $bookings
                ]);
            }

            return view('users.userbookings', [
                'routes' => $routes,
                'bookings' => $bookings
            ]);

        } catch (\Exception $e) {
            Log::error('User booking search error: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to search routes. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Unable to search routes. Please try again.');
        }
    }
}
