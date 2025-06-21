<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Booking;

class PaymentController extends Controller
{
    public function esewaCheckout(Request $request)
    {
        try {
            $request->validate([
                'route_id' => 'required',
                'selected_seat' => 'required',
                'price' => 'required|numeric',
                'customer_id' => 'required'
            ]);

            $booking = new Booking();
            $booking->route_id = $request->route_id;
            $booking->seat = $request->selected_seat;
            $booking->customer_id = $request->customer_id;
            $booking->amount = $request->price;
            $booking->payment_method = 'esewa';
            $booking->status = 'pending';
            $booking->payment_status = 'pending';
            $booking->save();

            // Store booking ID in session
            session(['esewa_booking_id' => $booking->id]);

            $data = [
                'amt' => $booking->amount,
                'pdc' => 0,
                'psc' => 0,
                'txAmt' => 0,
                'tAmt' => $booking->amount,
                'pid' => 'BUS-' . $booking->id,
                'scd' => config('services.esewa.merchant_code'),
                'su' => route('payment.esewa.success'),  // FIXED
                'fu' => route('payment.esewa.failure')   // FIXED
            ];

            $url = config('services.esewa.test_mode') 
                ? 'https://rc.esewa.com.np/epay/main'
                : 'https://esewa.com.np/epay/main';

            return response()->json([
                'success' => true,
                'redirect_url' => $url . '?' . http_build_query($data)
            ]);

        } catch (\Exception $e) {
            Log::error('eSewa checkout error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to initialize payment. Please try again.'
            ], 500);
        }
    }

    public function esewaSuccess(Request $request)
{
    try {
        Log::info('eSewa Success Callback Hit');

        $bookingId = session('esewa_booking_id');
        Log::info('Booking ID from session', ['bookingId' => $bookingId]);

        if (!$bookingId) {
            $oid = $request->query('oid');
            Log::info('OID from query', ['oid' => $oid]);

            if ($oid && preg_match('/BUS-(\d+)/', $oid, $matches)) {
                $bookingId = $matches[1];
                Log::info('Extracted bookingId from OID', ['bookingId' => $bookingId]);
            }
        }

        if (!$bookingId) {
            throw new \Exception('Booking ID not found');
        }

        $booking = Booking::find($bookingId);
        Log::info('Booking lookup', ['booking' => $booking]);

        if (!$booking) {
            throw new \Exception('Booking not found');
        }

        $booking->payment_status = 'completed';
        $booking->status = 'confirmed';
        $booking->save();

        session()->forget('esewa_booking_id');

        return redirect()->route('booking.success', ['id' => $booking->id])
            ->with('success', 'Payment successful! Your booking has been confirmed.');
    } catch (\Exception $e) {
        Log::error('eSewa success callback error', ['message' => $e->getMessage()]);
        return redirect()->route('booking.failure')
            ->with('error', 'Booking not found.');
    }
}

    public function esewaFailure(Request $request)
    {
        try {
            $bookingId = session('esewa_booking_id');
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                if ($booking) {
                    $booking->payment_status = 'failed';
                    $booking->save();
                }
                session()->forget('esewa_booking_id');
            }

            return redirect()->route('booking.failure')
                ->with('error', 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('eSewa failure callback error: ' . $e->getMessage());
            return redirect()->route('booking.failure')
                ->with('error', 'Payment process failed. Please contact support.');
        }
    }

    public function verifyKhaltiPayment(Request $request)
    {
        $request->validate([
            'khalti_token' => 'required|string',
            'amount' => 'required|numeric',
            'route_id' => 'required',
            'selected_seat' => 'required',
            'customer_id' => 'required',
        ]);

        $token = $request->input('khalti_token');
        $amount = $request->input('amount');
        $secretKey = config('services.khalti.secret_key');
        $testMode = config('services.khalti.test_mode');
        $verifyUrl = $testMode
            ? 'https://khalti.com/api/v2/payment/verify/'
            : 'https://khalti.com/api/v2/payment/verify/';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $secretKey,
                'Accept' => 'application/json',
            ])->post($verifyUrl, [
                'token' => $token,
                'amount' => $amount,
            ]);

            if ($response->successful() && isset($response['idx'])) {
                $booking = Booking::where('route_id', $request->route_id)
                    ->where('seat', $request->selected_seat)
                    ->where('customer_id', $request->customer_id)
                    ->first();

                if (!$booking) {
                    $booking = new Booking();
                    $booking->route_id = $request->route_id;
                    $booking->customer_id = $request->customer_id;
                    $booking->seat = $request->selected_seat;
                    $booking->price = $amount / 100;
                    $booking->status = 'Booked';
                    $booking->payment_status = 'completed';
                    $booking->payment_method = 'khalti';
                    $booking->save();
                } else {
                    $booking->payment_status = 'completed';
                    $booking->payment_method = 'khalti';
                    $booking->save();
                }

                return response()->json([
                    'success' => true,
                    'redirect_url' => route('booking.confirmation', ['id' => $booking->id]),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response['detail'] ?? 'Khalti payment verification failed.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Khalti payment verification error: ' . $e->getMessage(),
            ], 400);
        }
    }

   public function initiateEsewaPayment($bookingId)
{
    try {
        $booking = Booking::findOrFail($bookingId);

        if ($booking->payment_status === 'completed') {
            return redirect()->route('booking.success', ['id' => $booking->id])
                ->with('info', 'This booking has already been paid for.');
        }

        $amount = number_format($booking->price, 2, '.', '');

        $data = [
            'amt' => $amount,
            'pdc' => 0,
            'psc' => 0,
            'txAmt' => 0,
            'tAmt' => $amount,
            'pid' => 'BUS-' . $booking->id,
            'scd' => config('services.esewa.merchant_code'),
            'su' => route('payment.esewa.success'),
            'fu' => route('payment.esewa.failure'),
            'pn' => 'Bus Ticket #' . $booking->id
        ];

        // Store booking in session (optional)
        session(['esewa_booking_id' => $booking->id]);

        $paymentUrl = config('services.esewa.test_mode', true)
            ? config('services.esewa.test_url', 'https://uat.esewa.com.np/epay/main')
            : config('services.esewa.live_url', 'https://esewa.com.np/epay/main');

        if (!$paymentUrl) {
            throw new \Exception('eSewa payment URL not configured');
        }

        Log::info('Redirecting to eSewa', ['url' => $paymentUrl, 'data' => $data]);

        return view('payment.esewa-redirect', [
            'data' => $data,
            'url' => $paymentUrl
        ]);
    } catch (\Exception $e) {
        Log::error('eSewa payment initiation error: ' . $e->getMessage(), [
            'booking_id' => $bookingId,
            'trace' => $e->getTraceAsString()
        ]);

        return back()->with('error', 'Unable to initiate payment. Please try again later.');
    }
}

    public function userEsewaSuccess(Request $request)
    {
        try {
            Log::info('User eSewa Success Callback: full request', [
                'query' => $request->query(),
                'all' => $request->all()
            ]);

            $oid = $request->query('oid');
            if ($oid && preg_match('/BUS-(\\d+)/', $oid, $matches)) {
                $bookingId = $matches[1];
                Log::info('User eSewa Success: extracted bookingId', ['bookingId' => $bookingId]);
            } else {
                throw new \Exception('Invalid booking reference');
            }

            $booking = Booking::where('id', $bookingId)
                            ->where('customer_id', session('customer_id'))
                            ->first();

            if (!$booking) {
                Log::error('User eSewa Success: Booking not found or unauthorized', [
                    'bookingId' => $bookingId,
                    'customerId' => session('customer_id')
                ]);
                throw new \Exception('Booking not found or unauthorized');
            }

            $booking->payment_status = 'completed';
            $booking->status = 'confirmed';
            $booking->save();

            return redirect()->route('userdashboard')
                ->with('success', 'Payment successful! Your booking has been confirmed.');
        } catch (\Exception $e) {
            Log::error('User eSewa success error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            return redirect()->route('userdashboard')
                ->with('error', 'Unable to process payment confirmation.');
        }
    }

    public function userEsewaFailure(Request $request)
    {
        try {
            $oid = $request->query('oid');
            if ($oid && preg_match('/BUS-(\\d+)/', $oid, $matches)) {
                $bookingId = $matches[1];
                $booking = Booking::where('id', $bookingId)
                                ->where('customer_id', session('customer_id'))
                                ->first();

                if ($booking) {
                    $booking->payment_status = 'failed';
                    $booking->save();
                }
            }

            return redirect()->route('userdashboard')
                ->with('error', 'Payment failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('User eSewa failure error: ' . $e->getMessage());
            return redirect()->route('userdashboard')
                ->with('error', 'Payment process failed. Please contact support if you were charged.');
        }
    }

    public function adminEsewaSuccess(Request $request)
    {
        try {
            $oid = $request->query('oid');
            if ($oid && preg_match('/BUS-(\\d+)/', $oid, $matches)) {
                $bookingId = $matches[1];
                $booking = Booking::findOrFail($bookingId);
                $booking->payment_status = 'completed';
                $booking->status = 'confirmed';
                $booking->save();
            }

            return redirect()->route('bookings.index')
                ->with('success', 'Payment successful! Booking has been confirmed.');
        } catch (\Exception $e) {
            Log::error('Admin eSewa success error: ' . $e->getMessage());
            return redirect()->route('bookings.index')
                ->with('error', 'Unable to process payment confirmation.');
        }
    }

    public function adminEsewaFailure(Request $request)
    {
        try {
            $oid = $request->query('oid');
            if ($oid && preg_match('/BUS-(\\d+)/', $oid, $matches)) {
                $bookingId = $matches[1];
                $booking = Booking::find($bookingId);
                if ($booking) {
                    $booking->payment_status = 'failed';
                    $booking->save();
                }
            }

            return redirect()->route('bookings.index')
                ->with('error', 'Payment failed.');
        } catch (\Exception $e) {
            Log::error('Admin eSewa failure error: ' . $e->getMessage());
            return redirect()->route('bookings.index')
                ->with('error', 'Payment process failed.');
        }
    }
}
