<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use App\Models\Route;
use App\Models\Customer;
use Exception;

class EsewaPaymentController extends Controller
{
    // Checkout for eSewa
    public function checkout(Request $request)
    {
        try {
            $validated = $request->validate([
                'route_id' => 'required|exists:routes,id',
                'customer_id' => 'required|exists:customers,id',
                'selected_seat' => 'required|string',
                'price' => 'required|numeric|min:0',
                'payment_method' => 'required|in:esewa'
            ]);

            // Set session for customer
            if (!session('customer_id')) {
                session(['customer_id' => $validated['customer_id']]);
            }

            if (!session('customer_id') || session('customer_id') != $validated['customer_id']) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Please login to continue with payment.'], 401);
                }
                return redirect()->route('userlogin')->with('error', 'Please login to continue with payment.');
            }

            // Seat availability check
            $seatTaken = Booking::where('route_id', $validated['route_id'])
                ->where('seat', $validated['selected_seat'])
                ->whereIn('status', ['Booked', 'confirmed'])
                ->where(function ($q) {
                    $q->where('payment_status', 'completed')->orWhere('payment_method', 'cash');
                })->exists();

            if ($seatTaken) {
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => 'Seat already booked.'], 409);
                }
                return redirect()->route('userdashboard')->with('error', 'This seat has already been booked.');
            }

            $route = Route::findOrFail($validated['route_id']);
            $customer = Customer::findOrFail($validated['customer_id']);

            // Create booking
            $booking = Booking::create([
                'route_id' => $route->id,
                'customer_id' => $customer->id,
                'seat' => $validated['selected_seat'],
                'price' => $validated['price'],
                'status' => 'Booked',
                'payment_status' => 'pending',
                'payment_method' => 'esewa',
                'contact_number' => $customer->customer_contact,
                'bus_id' => $route->bus_id,
                'bus_name' => $route->bus->bus_name,
                'bus_number' => $route->bus->bus_number,
                'source' => $route->source,
                'destination' => $route->destination
            ]);

            return $this->redirectToEsewa($booking, $request->wantsJson());
        } catch (Exception $e) {
            Log::error('eSewa checkout error: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Checkout failed.'], 500);
            }
            return redirect()->route('userdashboard')->with('error', 'Checkout failed. Please try again.');
        }
    }

    // Redirect to eSewa
    public function redirectToEsewa(Booking $booking, $isJson = false)
    {
        $amount = $booking->price;
        $tax = 0; $psc = 0; $pdc = 0;
        $total = $amount + $tax;
        $pid = $booking->id;
        $success = route('esewa.success') . '?booking_id=' . $booking->id;
        $fail = route('esewa.failure') . '?booking_id=' . $booking->id;
        $scd = config('services.esewa.merchant_id', 'EPAYTEST');

        session(['esewa_transaction' => [
            'booking_id' => $booking->id,
            'transaction_uuid' => $pid,
            'amount' => $amount,
        ]]);

        if ($isJson) {
            return response()->json([
                'success' => true,
                'redirect_url' => "https://esewa.com.np/epay/main?" . http_build_query([
                    'amt' => $amount,
                    'txAmt' => $tax,
                    'psc' => $psc,
                    'pdc' => $pdc,
                    'tAmt' => $total,
                    'pid' => $pid,
                    'scd' => $scd,
                    'su' => $success,
                    'fu' => $fail,
                ])
            ]);
        }

        return redirect()->away("https://esewa.com.np/epay/main?" . http_build_query([
            'amt' => $amount,
            'txAmt' => $tax,
            'psc' => $psc,
            'pdc' => $pdc,
            'tAmt' => $total,
            'pid' => $pid,
            'scd' => $scd,
            'su' => $success,
            'fu' => $fail,
        ]));
    }

    // Success callback
    public function success(Request $request)
    {
        try {
            $transaction = session('esewa_transaction');
            $booking = $transaction['booking_id'] ?? $request->booking_id ? Booking::find($request->booking_id) : null;

            if (!$booking) {
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => 'Booking not found'], 404)
                    : view('payment.error', ['message' => 'Booking not found.']);
            }

            $booking->update(['status' => 'confirmed', 'payment_status' => 'completed']);
            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => 'esewa',
                'amount' => $transaction['amount'] ?? $booking->price,
                'transaction_id' => $request->refId,
                'status' => 'completed',
                'payment_date' => now()
            ]);

            $customer = $booking->customer;
            if ($customer) {
                session(['customer_id' => $customer->id, 'customer_name' => $customer->customer_name]);
                session()->save();
            }

            session()->forget('esewa_transaction');
            session(['new_booking' => $booking]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful, booking confirmed',
                    'booking' => $booking
                ]);
            }

            return redirect()->route('userdashboard')->with('success', 'Payment successful! Your booking is confirmed.');
        } catch (\Exception $e) {
            Log::error('eSewa success error: ' . $e->getMessage());
            return $request->wantsJson()
                ? response()->json(['success' => false, 'message' => 'Payment processing error'], 500)
                : redirect()->route('userdashboard')->with('error', 'An error occurred while processing your payment.');
        }
    }

    // Failure callback
    public function failure(Request $request)
    {
        try {
            $booking = Booking::findOrFail($request->booking_id);
            $booking->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            session()->forget('esewa_transaction');

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment failed or cancelled', 'booking' => $booking]);
            }

            return redirect()->route('userdashboard')->with('error', 'Payment failed or was cancelled.');
        } catch (Exception $e) {
            Log::error('eSewa failure: ' . $e->getMessage(), ['booking_id' => $request->booking_id]);
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error handling payment failure'], 500);
            }
            return redirect()->route('userdashboard')->with('error', 'An unexpected error occurred while handling payment failure.');
        }
    }
}
