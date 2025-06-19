<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */    public function handle(Request $request, Closure $next)
    {
        // Ensure session is started and working properly
        if (!$request->session()->isStarted()) {
            $request->session()->start();
        }
        
        // Perform session validity check
        if (session()->has('_token')) {
            $now = now()->timestamp;
            session(['last_activity' => $now]);
        } else {
            // Session appears to be broken - regenerate it
            session()->regenerate(true);
            Log::warning('CustomerAuth: Session appears to be invalid, regenerated');
        }
        
        // Enhanced logging for debugging
        Log::info('CustomerAuth Middleware: Session Data', [
            'session_keys' => array_keys(session()->all()), // Just log keys for privacy/security
            'path' => $request->path(),
            'route' => $request->route() ? $request->route()->getName() : 'unknown',
            'customer_id' => session('customer_id'),
            'uri' => $request->getUri(),
            'method' => $request->method(),
            'session_id' => session()->getId(),
            'has_customer' => session()->has('customer_id'),
            'headers' => [
                'referer' => $request->headers->get('referer'),
                'user_agent' => $request->headers->get('user-agent')
            ]
        ]);

        \Log::info('CustomerAuth: session', [
            'customer_id' => session('customer_id'),
            'session_all' => session()->all(),
        ]);

        if (!session('customer_id')) {
            Log::warning('CustomerAuth: No customer_id in session - attempting recovery', [
                'path' => $request->path(),
                'redirect_url' => url()->current(),
                'referer' => $request->headers->get('referer')
            ]);
            
            // Recovery strategies in priority order:
            
            // 1. Check flash data for new_booking
            if (session()->has('new_booking')) {
                $booking = session('new_booking');
                if ($booking && $booking->customer_id) {
                    try {
                        $customer = \App\Models\Customer::find($booking->customer_id);
                        if ($customer) {
                            session(['customer_id' => $customer->id, 'customer_name' => $customer->customer_name]);
                            Log::info('CustomerAuth: Recovered session from flash booking data', [
                                'customer_id' => $customer->id,
                                'booking_id' => $booking->id
                            ]);
                            return $next($request);
                        }
                    } catch (\Exception $e) {
                        Log::error('CustomerAuth: Error recovering from flash data: ' . $e->getMessage());
                    }
                }
            }
              // 2. Check if coming from payment gateway or has payment success indicator
            $referer = $request->headers->get('referer');
            $paymentReferers = ['payment/esewa/success', 'esewa.com', 'esewa.com.np', 'khalti.com'];
            $isFromPayment = false;
            $hasPaymentSuccess = session('payment_success') || session('esewa_transaction_id');
            
            foreach ($paymentReferers as $paymentReferer) {
                if ($referer && str_contains($referer, $paymentReferer)) {
                    $isFromPayment = true;
                    break;
                }
            }
            
            // Also check if we have current_booking with completed payment
            $currentBooking = session('current_booking');
            $hasCompletedBooking = $currentBooking && 
                                 isset($currentBooking->payment_status) && 
                                 $currentBooking->payment_status === 'completed';
            
            if ($isFromPayment || $hasPaymentSuccess || $hasCompletedBooking) {
                Log::info('CustomerAuth: Payment flow detected, attempting recovery', [
                    'from_payment' => $isFromPayment,
                    'has_payment_success' => $hasPaymentSuccess,
                    'has_completed_booking' => $hasCompletedBooking,
                    'referer' => $referer
                ]);
                
                // Try to find customer from current_booking first
                if ($hasCompletedBooking) {
                    try {
                        $customer = \App\Models\Customer::find($currentBooking->customer_id);
                        if ($customer) {
                            session([
                                'customer_id' => $customer->id, 
                                'customer_name' => $customer->customer_name
                            ]);
                            session()->save();
                            Log::info('CustomerAuth: Recovered session from current_booking', [
                                'customer_id' => $customer->id,
                                'booking_id' => $currentBooking->id
                            ]);
                            return $next($request);
                        }
                    } catch (\Exception $e) {
                        Log::error('CustomerAuth: Error recovering from current_booking: ' . $e->getMessage());
                    }
                }
                
                // Try to find most recent completed booking
                try {
                    $latestBooking = \App\Models\Booking::where('payment_status', 'completed')
                        ->where('updated_at', '>=', now()->subMinutes(10)) // Recent payments only
                        ->orderBy('updated_at', 'desc')
                        ->first();
                    
                    if ($latestBooking && $latestBooking->customer_id) {
                        $customer = \App\Models\Customer::find($latestBooking->customer_id);
                        if ($customer) {
                            session([
                                'customer_id' => $customer->id, 
                                'customer_name' => $customer->customer_name,
                                'current_booking' => $latestBooking
                            ]);
                            session()->save();
                            Log::info('CustomerAuth: Recovered session from recent booking', [
                                'customer_id' => $customer->id,
                                'booking_id' => $latestBooking->id
                            ]);
                            return $next($request);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('CustomerAuth: Error recovering session: ' . $e->getMessage());
                }
            }
            
            return redirect()->route('userlogin')
                ->with('error', 'Please login to access this page')
                ->with('redirect_after_login', url()->current());
        }

        Log::info('CustomerAuth: Authentication successful', ['customer_id' => session('customer_id')]);
        return $next($request);
    }
}
