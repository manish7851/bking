<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Booking;

class PaymentController extends Controller
{
    public function initiateEsewaPayment($bookingId)
    {
        // Logic to initiate eSewa payment
        // Instead of JSON, show a user-friendly UI page
        $booking = \App\Models\Booking::find($bookingId);
        if (!$booking) {
            return view('payment.error', ['message' => 'Booking not found.']);
        }
        return view('payment.esewa_form', compact('booking'));
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
            : 'https://khalti.com/api/v2/payment/verify/'; // Same for live, but keep for clarity

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $secretKey,
                'Accept' => 'application/json',
            ])->post($verifyUrl, [
                'token' => $token,
                'amount' => $amount,
            ]);

            if ($response->successful() && isset($response['idx'])) {
                // Payment verified, create booking or mark as paid
                $booking = Booking::where('route_id', $request->route_id)
                    ->where('seat', $request->selected_seat)
                    ->where('customer_id', $request->customer_id)
                    ->first();
                if (!$booking) {
                    // Create booking if not exists
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
}
