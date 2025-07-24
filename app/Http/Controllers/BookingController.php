<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Route;
use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\ESewaPaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TicketDetailsMail;
use Illuminate\Support\Facades\Http;

class BookingController extends Controller
{
    public function index()
    {
        try {
            $bookings = Booking::with(['customer', 'route.bus'])
                ->orderBy('created_at', 'desc')
                ->get();
            $customers = Customer::all();
            $routes = Route::all();
            return view('booking.booking_page', compact('bookings', 'customers', 'routes'));
        } catch (\Exception $e) {
            return $this->handleBookingError($e, 'index');
        }
    }

    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->store($request);
        }

        try {
            $route = Route::with('bus')->findOrFail($request->route_id);
            
            if (!$route->bus) {
                Log::error('Route has no bus assigned', [
                    'route_id' => $request->route_id,
                    'source' => $route->source,
                    'destination' => $route->destination
                ]);
                return back()->with('error', 'Sorry, this route currently has no bus assigned. Please select another route or try again later.');
            }

            $booked_seats = Booking::where('route_id', $request->route_id)
                ->whereIn('status', ['Booked', 'confirmed'])
                ->pluck('seat')
                ->toArray();

            $customer = null;
            $isLoggedIn = false;
            if (session('customer_id')) {
                $customer = Customer::findOrFail(session('customer_id'));
                $isLoggedIn = true;
            }

            return view('booking.create', compact('route', 'booked_seats', 'customer', 'isLoggedIn'));
        } catch (\Exception $e) {
            Log::error('Booking create error: ' . $e->getMessage());
            return back()->with('error', 'Unable to create booking.');
        }
    }

    private function handleBookingError(\Exception $e, string $context, $request = null)
    {
        $errorId = uniqid('booking_');
        $logContext = [
            'error_id' => $errorId,
            'context' => $context,
            'trace' => $e->getTraceAsString()
        ];

        if ($request) {
            $logContext['request_data'] = $request->all();
        }

        Log::error("Booking error ({$errorId}): " . $e->getMessage(), $logContext);

        if ($request && $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error_id' => $errorId,
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing your booking.'
            ], 500);
        }

        return back()->with('error', 'An error occurred while processing your booking. Reference: ' . $errorId);
    }

    public function store(Request $request)
    {
        try {
            // Only require session for user self-booking (frontend), not for admin/staff modal bookings
            if (!session('customer_id') && !($request->has('customer_id') && !empty($request->customer_id))) {
                return response()->json([
                    'success' => false,
                    'redirect_to_login' => true,
                    'message' => 'Please login to complete your booking',
                    'login_url' => route('userlogin') . '?redirect_after_login=' . urlencode(route('bookings.create', ['route_id' => $request->route_id]))
                ], 401);
            }

            // Handle both 'selected_seat' and 'seat' field names
            $seatValue = $request->input('selected_seat') ?: $request->input('seat');
            if (!$seatValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a seat before booking.'
                ], 422);
            }

            // Patch: If payment_method is missing, default to 'esewa' for admin/staff bookings
            $requestPaymentMethod = $request->input('payment_method');
            if (!$requestPaymentMethod) {
                $request->merge(['payment_method' => 'esewa']);
            }

            $validated = $request->validate([
                'route_id' => 'required|exists:routes,id',
                'customer_id' => 'required|exists:customers,id',
                'price' => 'required|numeric',
                'payment_method' => 'required|in:esewa'
            ]);

            // Add the seat value to validated data
            $validated['selected_seat'] = $seatValue;

            $route = Route::with('bus')->findOrFail($validated['route_id']);
            if (!$route->bus) {
                throw new \Exception('No bus assigned to this route.');
            }

            $customer = Customer::findOrFail($validated['customer_id']);                $existingBooking = Booking::where('route_id', $validated['route_id'])
                ->where('seat', $validated['selected_seat'])
                ->whereIn('status', ['Booked', 'confirmed'])
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'This seat has already been booked. Please select another seat.'
                ], 400);
            }

            $payment_method = $validated['payment_method'];

            $booking = new Booking();
            $booking->route_id = $validated['route_id'];
            $booking->customer_id = $validated['customer_id'];
            $booking->seat = $validated['selected_seat'];
            $booking->price = $validated['price'];
            $booking->status = 'Booked';
            $booking->contact_number = $customer->customer_contact;
            if ($route->bus) {
                $booking->bus_id = $route->bus->id;
                $booking->bus_name = $route->bus->bus_name;
                $booking->bus_number = $route->bus->bus_number;
            }
            // Set source and destination with proper formatting
            $booking->source = trim($route->source);
            $booking->destination = trim($route->destination);
            $booking->user_id = null;
            $booking->save();

            session(['current_booking' => $booking]);

            if ($payment_method === 'esewa') {
                $esewaService = new ESewaPaymentService();
                // Set isUserBooking to true for user bookings, false for admin bookings
                $isUserBooking = request()->is('userbookings/*');
                $response = $esewaService->initiatePayment($validated['price'], $booking->id, $isUserBooking);

                if ($response['success']) {
                    return response()->json([
                        'success' => true,
                        'redirect_url' => $response['redirect_url']
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment initiation failed. Please try again.'
                    ], 400);
                }
            }

            return response()->json([
                'success' => true,
                'redirect_url' => route('booking.confirmation', ['id' => $booking->id])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors as JSON
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?? 'Validation failed. Please check your input.'
            ], 422);
        } catch (\Exception $e) {
            // Log full exception for debugging
            Log::error('Booking error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'validated_data' => isset($validated) ? $validated : null,
                'route' => isset($route) ? $route : null,
                'customer' => isset($customer) ? $customer : null,
            ]);
            // Show detailed error in debug mode, generic otherwise
            $message = config('app.debug') ? $e->getMessage() : 'Unable to process booking. Please try again.';
            return response()->json([
                'success' => false,
                'message' => $message
            ], 500);
        }
    }    public function checkSeatAvailability(Request $request)
    {
        try {
            $request->validate([
                'route_id' => 'required|exists:routes,id',
                'seat' => 'required|string'
            ]);
            
            $isBooked = Booking::where('route_id', $request->route_id)
                ->where('seat', $request->seat)
                ->whereIn('status', ['Booked', 'confirmed'])
                ->exists();

            return response()->json([
                'available' => !$isBooked,
                'seat' => $request->seat,
                'route_id' => $request->route_id
            ]);
        } catch (\Exception $e) {
            Log::error('Seat availability check error: ' . $e->getMessage());
            return response()->json([
                'available' => false,
                'message' => 'Error checking seat availability'
            ], 500);
        }
    }

    public function getBookedSeats($route_id)
    {
        try {
            $bookedSeats = Booking::where('route_id', $route_id)
                ->whereIn('status', ['Booked', 'confirmed'])
                ->pluck('seat')
                ->toArray();

            return response()->json($bookedSeats);
        } catch (\Exception $e) {
            Log::error('Failed to get booked seats: ' . $e->getMessage());
            return response()->json(['error' => 'Could not retrieve booked seats.'], 500);
        }
    }

    public function fetchRoutes(Request $request)
    {
        try {
            $query = Route::with('bus');
            
            if ($request->filled('source')) {
                $query->where('source', 'like', '%' . $request->source . '%');
            }
            if ($request->filled('destination')) {
                $query->where('destination', 'like', '%' . $request->destination . '%');
            }

            if ($request->filled('date')) {
                // Compare only the date part of trip_date with the provided date
                $query->whereDate('trip_date', '=', trim($request->date));
            }
            
            $routes = $query->get();

            if ($routes->count() === 1) {
                if (session('customer_id')) {
                    return redirect()->route('userbookings.create', ['route_id' => $routes->first()->id]);
                }
                if (Auth::check()) {
                    return redirect()->route('admin.bookings.create', ['route_id' => $routes->first()->id]);
                }
            }

            return view('users.userbooking', ['routes' => $routes]);
        } catch (\Exception $e) {
            Log::error('Fetch routes error: ' . $e->getMessage());
            return back()->with('error', 'Unable to fetch routes');
        }
    }

    public function confirmation($id)
    {
        try {
            $booking = Booking::with(['route.bus', 'customer'])->findOrFail($id);

            if (!session('customer_id') || $booking->customer_id != session('customer_id')) {
                return redirect()->route('userlogin')->with('error', 'Access denied.');
            }

            return view('booking.confirmation', compact('booking'));
        } catch (\Exception $e) {
            Log::error('Booking confirmation error: ' . $e->getMessage());
            return back()->with('error', 'Unable to load booking confirmation.');
        }
    }

    public function bookingSuccess($id)
    {
        try {
            $booking = Booking::with(['route.bus', 'customer'])->findOrFail($id);

            if (!session('customer_id') || $booking->customer_id !== session('customer_id')) {
                return redirect()->route('userlogin')->with('error', 'Access denied.');
            }

            return view('booking.success', compact('booking'));
        } catch (\Exception $e) {
            Log::error('Booking success error: ' . $e->getMessage());
            return back()->with('error', 'Unable to load booking success page.');
        }
    }

    public function destroy($id)
    {
        try {
            $booking = Booking::findOrFail($id);

            if (Auth::check()) {
                $booking->delete();
                return redirect()->route('bookings_page')->with('success', 'Booking deleted successfully.');
            }

            if (session('customer_id') && $booking->customer_id === session('customer_id')) {
                if ($booking->status === 'pending' || $booking->status === 'cancelled') {
                    $booking->delete();
                    return redirect()->route('userdashboard')->with('success', 'Booking cancelled successfully.');
                }
                return back()->with('error', 'Cannot cancel a confirmed booking.');
            }

            return back()->with('error', 'Unauthorized access.');
        } catch (\Exception $e) {
            Log::error('Booking deletion error: ' . $e->getMessage());
            return back()->with('error', 'Unable to delete booking.');
        }
    }

    public function showUserTickets()
    {
        if (!session('customer_id')) {
            return redirect()->route('userlogin')
                ->with('error', 'Please log in to view your tickets.');
        }

        $bookings = Booking::where('customer_id', session('customer_id'))
            ->orderBy('created_at', 'desc')
            ->with(['route.bus'])
            ->get();

        return view('userdashboard.ticket_history', compact('bookings'));
    }

    public function userBookings(Request $request)
    {
        if (!session('customer_id')) {
            return redirect()->route('userlogin')
                ->with('error', 'Please login to view your bookings')
                ->with('redirect_after_login', url()->current());
        }

        $bookings = Booking::where('customer_id', session('customer_id'))
            ->orderBy('created_at', 'desc')
            ->with(['route.bus'])
            ->get();

        return view('users.userbookings', compact('bookings'));
    }

    public function usersBookings(Request $request)
    {
        if (!session('customer_id')) {
            return redirect()->route('userlogin')
                ->with('error', 'Please login to view your bookings')
                ->with('redirect_after_login', url()->current());
        }

        $bookings = Booking::where('customer_id', session('customer_id'))
            ->orderBy('created_at', 'desc')
            ->with(['route.bus'])
            ->get();

        return view('users.userbookings', compact('bookings'));
    }

    public function update(Request $request, $id)
    {
        try {
            $booking = Booking::findOrFail($id);
            $validated = $request->validate([
                'customer_id' => 'required|exists:customers,id',
                'route_id' => 'required|exists:routes,id',
                'bus_id' => 'required|exists:buses,id',
                'bus_number' => 'required',
                'seat' => 'required',
                'price' => 'required|numeric',
                'payment_method' => 'required|in:cash,esewa,khalti'
            ]);

            // Check seat availability (skip if it's the same seat)
            if ($booking->seat !== $validated['seat']) {
                $seatTaken = Booking::where('route_id', $validated['route_id'])
                    ->where('seat', $validated['seat'])
                    ->where('id', '!=', $id)
                    ->whereIn('status', ['Booked', 'confirmed'])
                    ->exists();

                if ($seatTaken) {
                    throw new \Illuminate\Validation\ValidationException(
                        validator([], []),
                        response()->json(['message' => 'This seat is already booked.'], 422)
                    );
                }
            }

            $booking->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully!'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?? 'Validation failed.'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Booking update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update booking.'
            ], 500);
        }
    }

    public function edit(Request $request, $id)
    {
        try {
            $booking = Booking::with(['customer', 'route.bus'])->find($id);
            if (!$booking) {
                Log::warning('Edit attempted for non-existent booking', [
                    'booking_id' => $id,
                    'user_id' => Auth::id() ?? session('customer_id'),
                    'request_data' => $request->all()
                ]);
                
                $errorMsg = 'The booking you are trying to edit was not found. It may have been deleted or the link is invalid.';
                
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMsg
                    ], 404);
                }
                
                return back()->with('error', $errorMsg);
            }

            if ($request->ajax()) {
                $customerData = null;
                if ($booking->customer) {
                    $customerData = [
                        'customer_address' => $booking->customer->customer_address ?? '',
                        'customer_contact' => $booking->customer->customer_contact ?? '',
                    ];
                }
                
                return response()->json([
                    'id' => $booking->id,
                    'customer_id' => $booking->customer_id,
                    'bus_id' => $booking->bus_id,
                    'bus_number' => $booking->bus_number,
                    'route_id' => $booking->route_id,
                    'seat' => $booking->seat,
                    'price' => $booking->price,
                    'payment_method' => $booking->payment_method,
                    'customer' => $customerData,
                ]);
            }

            $customers = Customer::all();
            $routes = Route::all();
            $buses = Bus::all();
            
            return view('booking.edit', compact('booking', 'customers', 'routes', 'buses'));
        } catch (\Exception $e) {
            Log::error('Booking edit error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load booking details.'
                ], 500);
            }
            return back()->with('error', 'Unable to load booking details.');
        }
    }

    public function userCreate(Request $request)
    {
        try {
            $route = Route::with('bus')->findOrFail($request->route_id);
            
            if (!$route->bus) {
                return back()->with('error', 'Sorry, this route currently has no bus assigned. Please select another route.');
            }

            $booked_seats = Booking::where('route_id', $request->route_id)
                ->whereIn('status', ['Booked', 'confirmed'])
                ->pluck('seat')
                ->toArray();

            $customer = null;
            $isLoggedIn = false;
            if (session('customer_id')) {
                $customer = Customer::findOrFail(session('customer_id'));
                $isLoggedIn = true;
            }

            return view('booking.create', compact('route', 'booked_seats', 'customer', 'isLoggedIn'));
        } catch (\Exception $e) {
            Log::error('User booking create error: ' . $e->getMessage());
            return back()->with('error', 'Unable to create booking.');
        }
    }


    public function userStore(Request $request)
    {
        try {
            if (!session('customer_id')) {
                return response()->json([
                    'success' => false,
                    'redirect_to_login' => true,
                    'message' => 'Please login to complete your booking',
                    'login_url' => route('userlogin') . '?redirect_after_login=' . urlencode(route('userbookings.create'))
                ], 401);
            }

            $seatValue = $request->input('selected_seat') ?: $request->input('seat');
            if (!$seatValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select a seat before booking.'
                ], 422);
            }

            $validated = $request->validate([
                'route_id' => 'required|exists:routes,id',
                'price' => 'required|numeric',
                'payment_method' => 'required|in:esewa'
            ]);

            // Add the seat value and customer_id to validated data
            $validated['selected_seat'] = $seatValue;
            $validated['customer_id'] = session('customer_id');

            $route = Route::with('bus')->findOrFail($validated['route_id']);
            if (!$route->bus) {
                throw new \Exception('No bus assigned to this route.');
            }

            $customer = Customer::findOrFail($validated['customer_id']);

            $existingBooking = Booking::where('route_id', $validated['route_id'])
                ->where('seat', $validated['selected_seat'])
                ->whereIn('status', ['Booked', 'confirmed'])
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'This seat has already been booked. Please select another seat.'
                ], 400);
            }

            $booking = new Booking();
            $booking->route_id = $validated['route_id'];
            $booking->customer_id = $validated['customer_id'];
            $booking->seat = $validated['selected_seat'];
            $booking->price = $validated['price'];
            $booking->status = 'Booked';
            $booking->contact_number = $customer->customer_contact;
            $booking->bus_id = $route->bus->id;
            $booking->bus_name = $route->bus->bus_name;
            $booking->bus_number = $route->bus->bus_number;
            $booking->source = $route->source;
            $booking->destination = $route->destination;
            $booking->user_id = null;
            $booking->save();

            // Send ticket details email to the customer only if requested
            // if ($request->has('send_ticket_notification') && !empty($customer->email)) {
                try {
                    $apiKey = env('SENDGRID_API');
                    if (!$apiKey) {
                        throw new \Exception('SendGrid API key is not configured.');
                    }

                    // Render the mailable to get the HTML content.
                    $htmlContent = (new TicketDetailsMail($booking))->render();

                    $response = Http::withToken($apiKey)->post('https://api.sendgrid.com/v3/mail/send', [
                        'personalizations' => [
                            [
                                'to' => [['email' => $customer->email]],
                                'subject' => 'Your Ticket Details'
                            ]
                        ],
                        'from' => ['email' => 'gmanish092@gmail.com', 'name' => config('mail.from.name', 'Bus Booking')],
                        'content' => [
                            [
                                'type' => 'text/html',
                                'value' => $htmlContent
                            ]
                        ]
                    ]);

                    if ($response->failed()) {
                        Log::error('Failed to send ticket email via SendGrid', [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error preparing or sending email via SendGrid: ' . $e->getMessage());
                }
            //}
            // Always notify admin
            // try {
            //     $apiKey = env('SENDGRID_API');
            //     if (!$apiKey) {
            //         throw new \Exception('SendGrid API key is not configured.');
            //     }

            //     // Render the mailable to get the HTML content.
            //     $htmlContent = (new TicketDetailsMail($booking))->render();

                // $response = Http::withToken($apiKey)->post('https://api.sendgrid.com/v3/mail/send', [
                //     'personalizations' => [
                //         [
                //             'to' => [['email' => 'admin@example.com']],
                //             'subject' => 'New Booking Notification'
                //         ]
                //     ],
                //     'from' => ['email' => config('mail.from.address', 'noreply@example.com'), 'name' => config('mail.from.name', 'Bus Booking System')],
                //     'content' => [
                //         [
                //             'type' => 'text/html',
                //             'value' => $htmlContent
                //         ]
                //     ]
                // ]);

                // if ($response->failed()) {
                //     Log::error('Failed to send ticket email to admin via SendGrid', [
                //         'status' => $response->status(),
                //         'body' => $response->body()
                //     ]);
                // }
            // } catch (\Exception $e) {
            //     Log::error('Error preparing or sending admin email via SendGrid: ' . $e->getMessage());
            // }

            session(['current_booking' => $booking]);

            $esewaService = new ESewaPaymentService();
            $response = $esewaService->initiatePayment($validated['price'], $booking->id);

            if ($response['success']) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $response['redirect_url']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initiation failed. Please try again.'
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first() ?? 'Validation failed. Please check your input.'
            ], 422);
        } catch (\Exception $e) {
            Log::error('User booking error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your booking. Please try again.'
            ], 500);
        }
    }

        /**
     * Save pickup and dropoff details for a booking (AJAX).
     */
    public function savePickupDropoff(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'pickup_location' => 'nullable|string|max:255',
            'pickup_location_latitude' => 'nullable|numeric|max:255',
            'pickup_location_longitude' => 'nullable|numeric|max:255',
            'pickup_remark' => 'nullable|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'dropoff_location_latitude' => 'nullable|numeric|max:255',
            'dropoff_location_longitude' => 'nullable|numeric|max:255',
            'dropoff_remark' => 'nullable|string|max:255',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);
        $booking->pickup_location = $validated['pickup_location_latitude'] . ',' . $validated['pickup_location_longitude'];
        $booking->pickup_remark = $validated['pickup_remark'] ?? null;
        $booking->dropoff_location = $validated['dropoff_location_latitude'] . ',' . $validated['dropoff_location_longitude'];
        $booking->dropoff_remark = $validated['dropoff_remark'] ?? null;
        $booking->save();
        return redirect()->back()->with('success', 'Pickup/Dropoff details saved successfully.');

    }

}
