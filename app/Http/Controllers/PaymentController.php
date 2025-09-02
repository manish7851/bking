<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Booking;

class PaymentController extends Controller
{
    // Initiate eSewa Payment
    public function esewaCheckout(Request $request)
    {
        $request->validate([
            'route_id' => 'required',
            'selected_seat' => 'required',
            'price' => 'required|numeric',
            'customer_id' => 'required'
        ]);

        $booking = Booking::create([
            'route_id' => $request->route_id,
            'seat' => $request->selected_seat,
            'customer_id' => $request->customer_id,
            'price' => $request->price,
            'payment_method' => 'esewa',
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        session(['esewa_booking_id' => $booking->id]);

        $data = [
            'amt' => $booking->price,
            'pdc' => 0,
            'psc' => 0,
            'txAmt' => 0,
            'tAmt' => $booking->price,
            'pid' => 'BUS-' . $booking->id,
            'scd' => config('services.esewa.merchant_code'),
            'su' => route('payment.esewa.success'),
            'fu' => route('payment.esewa.failure')
        ];

        $url = config('services.esewa.test_mode', true)
            ? 'https://uat.esewa.com.np/epay/main'
            : 'https://esewa.com.np/epay/main';

        // JSON request
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => $url . '?' . http_build_query($data)
            ]);
        }

        // Web request
        return view('payment.esewa-redirect', ['data' => $data, 'url' => $url]);
    }

    // eSewa Success Callback
    public function esewaSuccess(Request $request)
    {
        try {
            $bookingId = session('esewa_booking_id') ?? null;
            if (!$bookingId && $request->query('oid')) {
                if (preg_match('/BUS-(\d+)/', $request->query('oid'), $matches)) {
                    $bookingId = $matches[1];
                }
            }

            if (!$bookingId) {
                throw new \Exception('Booking ID not found');
            }

            $booking = Booking::findOrFail($bookingId);
            $booking->payment_status = 'completed';
            $booking->status = 'confirmed';
            $booking->save();
            session()->forget('esewa_booking_id');

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Payment completed', 'booking_id' => $booking->id]);
            }

            return redirect()->route('userdashboard')
                ->with('success', 'Payment successful! Booking confirmed.');
        } catch (\Exception $e) {
            Log::error('eSewa success error: '.$e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }

            return redirect()->route('userdashboard')->with('error', 'Payment processing failed.');
        }
    }

    // eSewa Failure Callback
    public function esewaFailure(Request $request)
    {
        $bookingId = session('esewa_booking_id') ?? null;
        if ($bookingId) {
            $booking = Booking::find($bookingId);
            if ($booking) {
                $booking->payment_status = 'failed';
                $booking->status = 'cancelled';
                $booking->save();
            }
            session()->forget('esewa_booking_id');
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Payment failed']);
        }

        return redirect()->route('userdashboard')->with('error', 'Payment failed or cancelled.');
    }
}
