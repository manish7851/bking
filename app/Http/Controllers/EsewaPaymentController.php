<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Route;
use App\Models\Customer;
use Exception;

class EsewaPaymentController extends Controller
{
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

            // If session('customer_id') is not set but a valid customer_id is present, set it (for admin/staff booking)
            if (!session('customer_id') && !empty($validated['customer_id'])) {
                session(['customer_id' => $validated['customer_id']]);
            }
            if (!session('customer_id') || session('customer_id') != $validated['customer_id']) {
                return redirect()->route('userlogin')->with('error', 'Please login to continue with payment.');
            }

            if ($validated['payment_method'] !== 'esewa') {
                return redirect()->route('userdashboard')->with('error', 'Invalid payment method.');
            }

            // Check seat availability
            $seatTaken = Booking::where('route_id', $validated['route_id'])
                ->where('seat', $validated['selected_seat'])
                ->whereIn('status', ['Booked', 'confirmed'])
                ->where(function ($q) {
                    $q->where('payment_status', 'completed')->orWhere('payment_method', 'cash');
                })->exists();

            if ($seatTaken) {
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

            return $this->redirectToEsewa($booking);
        } catch (Exception $e) {
            Log::error('eSewa checkout error: ' . $e->getMessage());
            return redirect()->route('userdashboard')->with('error', 'Checkout failed. Please try again.');
        }
    }    public function redirectToEsewa(Booking $booking)
    {
        $amount = $booking->price;
        $tax = 0;
        $psc = 0;
        $pdc = 0;
        $total = $amount + $tax;
        $pid = $booking->id;  // Booking ID नै use गर्ने
        $success = route('esewa.success') . '?booking_id=' . $booking->id;
        $fail = route('esewa.failure') . '?booking_id=' . $booking->id;
        $scd = config('services.esewa.merchant_id', 'EPAYTEST');

        session(['esewa_transaction' => [
            'booking_id' => $booking->id,
            'transaction_uuid' => $pid,
            'amount' => $amount,
        ]]);

        // Redirect user via GET to eSewa
        $query = http_build_query([
            'amt' => $amount,
            'txAmt' => $tax,
            'psc' => $psc,
            'pdc' => $pdc,
            'tAmt' => $total,
            'pid' => $pid,
            'scd' => $scd,
            'su' => $success,
            'fu' => $fail,
        ]);

        return redirect()->away("https://esewa.com.np/epay/main?$query");
    }

    public function success(Request $request)
    {
        try {
            if (!$request->has(['oid', 'amt', 'refId'])) {
                return redirect()->route('userdashboard')->with('error', 'Invalid response from eSewa. Please try again or contact support.');
            }

            $transaction = session('esewa_transaction');
            $booking = null;

            if ($transaction && isset($transaction['booking_id'])) {
                $booking = Booking::find($transaction['booking_id']);
            }

            // Session expire भएमा, URL बाट खोज्ने
            if (!$booking && $request->has('oid')) {
                $booking = Booking::find($request->oid);
            }

            if (!$booking) {
                return view('payment.error', ['message' => 'Booking not found.']);
            }

            // Confirm booking
            $booking->update([
                'status' => 'confirmed',
                'payment_status' => 'completed'
            ]);

            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => 'esewa',
                'amount' => $transaction['amount'] ?? $booking->price,
                'transaction_id' => $request->refId,
                'status' => 'completed',
                'payment_date' => now()
            ]);

            // Restore session for customer after payment (in case lost)
            $customer = $booking->customer;
            if ($customer) {
                session([
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->customer_name
                ]);
                session()->save();
            }

            session()->forget('esewa_transaction');
            session(['new_booking' => $booking]);

            return redirect()->route('userdashboard')->with('success', 'Payment successful! Your booking is confirmed.');
        } catch (\Exception $e) {
            Log::error('eSewa success error: ' . $e->getMessage());
            return redirect()->route('userdashboard')->with('error', 'An error occurred while processing your payment. Please try again or contact support.');
        }
    }

    public function failure(Request $request)
    {
        try {
            $booking = Booking::findOrFail($request->booking_id);

            $booking->update([
                'status' => 'cancelled',
                'payment_status' => 'failed'
            ]);

            session()->forget('esewa_transaction');

            return redirect()->route('userdashboard')->with('error', 'Payment failed or was cancelled. Please try again or contact support.');
        } catch (Exception $e) {
            Log::error('eSewa failure: ' . $e->getMessage(), [
                'booking_id' => $request->booking_id,
                'exception' => $e
            ]);
            return redirect()->route('userdashboard')->with('error', 'An unexpected error occurred while handling payment failure. Please contact support.');
        }
    }

    public function process(Request $request)
    {
        // Get booking data from session if not in request
        $bookingData = $request->all();
        if (empty($bookingData) && session()->has('pending_booking')) {
            $bookingData = session('pending_booking');
        }
        
        if (empty($bookingData)) {
            return redirect()->route('userbookings.search')
                ->with('error', 'Booking data not found');
        }

        // Ensure user is logged in
        if (!session('customer_id')) {
            session()->put('pending_booking', $bookingData);
            return redirect()->route('userlogin')
                ->with('message', 'Please login to complete your booking')
                ->with('redirect_after_login', route('payment.esewa.process'));
        }

        try {
            // Create a pending booking record
            $booking = new \App\Models\Booking([
                'route_id' => $bookingData['route_id'],
                'customer_id' => session('customer_id'),
                'bus_id' => $bookingData['bus_id'],
                'selected_seat' => $bookingData['selected_seat'],
                'payment_method' => 'esewa',
                'payment_status' => 'pending',
                'price' => $bookingData['price']
            ]);
            $booking->save();

            // Store booking in session
            session(['current_booking' => $booking]);

            // Generate eSewa payment form
            return view('payment.esewa-redirect', [
                'amount' => $booking->price,
                'productId' => $booking->id,
                'successUrl' => route('payment.esewa.success'),
                'failureUrl' => route('payment.esewa.failure')
            ]);
        } catch (\Exception $e) {
            \Log::error('eSewa payment process error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error processing payment. Please try again.');
        }
    }
}
