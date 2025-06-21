<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ESewaPaymentService
{    private $merchantId;
    private $baseUrl;
    private $verificationUrl;
    private $successUrl;
    private $failureUrl;
      public function __construct()
    {
        $this->merchantId = config('services.esewa.merchant_id');
        $this->baseUrl = config('services.esewa.url');
        $this->verificationUrl = config('services.esewa.verification_url');
        
        if (!$this->merchantId || !$this->baseUrl || !$this->verificationUrl) {
            throw new \RuntimeException('eSewa configuration is missing. Please check your .env file and services configuration.');
        }
    }    public function initiatePayment($amount, $bookingId, $isUserBooking = true, $productName = 'Bus Ticket')
    {
        try {
            // Validate amount
            if (!is_numeric($amount) || $amount <= 0) {
                throw new \InvalidArgumentException('Invalid amount provided');
            }

            // Set success and failure URLs based on booking type
            $this->successUrl = $isUserBooking 
                ? route('payment.user.esewa.success') 
                : route('payment.admin.esewa.success');
            $this->failureUrl = $isUserBooking 
                ? route('payment.user.esewa.failure') 
                : route('payment.admin.esewa.failure');

            // Format amounts with 2 decimal places
            $totalAmount = number_format($amount, 2, '.', '');
            $serviceCharge = 0;
            $deliveryCharge = 0;
            $taxAmount = 0;

            $params = [
                'amt' => $totalAmount,              // Product amount
                'psc' => $serviceCharge,            // Service charge
                'pdc' => $deliveryCharge,          // Delivery charge
                'txAmt' => $taxAmount,             // Tax amount
                'tAmt' => $totalAmount,            // Total amount
                'pid' => "BUS-{$bookingId}",       // Unique Product ID
                'scd' => $this->merchantId,        // Merchant ID
                'su' => $this->successUrl,         // Success URL
                'fu' => $this->failureUrl          // Failure URL
            ];

            Log::info('Initiating eSewa payment', [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'params' => $params,
                'merchant_id' => $this->merchantId,
                'redirect_url' => $this->baseUrl
            ]);

            $redirectUrl = $this->baseUrl . '?' . http_build_query($params);

            return [
                'success' => true,
                'redirect_url' => $redirectUrl
            ];
        } catch (\Exception $e) {            Log::error('eSewa payment initiation error', [
                'message' => $e->getMessage(),
                'booking_id' => $bookingId,
                'amount' => $amount,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Payment initiation failed'
            ];
        }
    }
    
        public function verifyPayment($pid, $rid, $amt, $refId)
    {
        // Use correct eSewa verification URL
        $url = "https://rc.esewa.com.np/epay/transrec";

        try {
            $response = Http::timeout(30)->get($url, [
                'pid' => $pid,
                'rid' => $refId,
                'amt' => $amt,
                'scd' => $this->merchantId
            ]);

            Log::info('eSewa payment verification', [
                'pid' => $pid,
                'rid' => $refId,
                'amt' => $amt,
                'url' => $url,
                'response' => $response->body()
            ]);

            // eSewa responds with 'Success' in response body for successful transactions
            if (str_contains($response->body(), 'Success')) {
                return [
                    'success' => true,
                    'message' => 'Thank you for your payment',
                    'refId' => $refId
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment verification failed',
                'refId' => $refId
            ];
        } catch (\Exception $e) {
            Log::error('eSewa verification error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Payment verification failed',
                'refId' => $refId
            ];
        }
    }
}
