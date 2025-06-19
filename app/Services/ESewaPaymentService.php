<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ESewaPaymentService
{
    private $merchantId = 'EPAYTEST';
    private $baseUrl;
    private $successUrl;
    private $failureUrl;    public function __construct()
    {
        // Use actual eSewa test environment URL
        $this->baseUrl = 'https://rc.esewa.com.np/epay/main';
        $this->successUrl = config('app.url') . '/payment/esewa/success';
        $this->failureUrl = config('app.url') . '/payment/esewa/failure';
    }public function initiatePayment($amount, $bookingId, $productName = 'Bus Ticket')
    {
        $params = [
            'amt' => $amount,
            'pdc' => 0,
            'psc' => 0,
            'txAmt' => 0,
            'tAmt' => $amount,
            'pid' => $bookingId,
            'scd' => $this->merchantId,
            'su' => $this->successUrl,
            'fu' => $this->failureUrl
        ];        Log::info('Initiating eSewa payment', [
            'booking_id' => $bookingId,
            'amount' => $amount,
            'params' => $params,
            'redirect_url' => $this->baseUrl
        ]);

        $redirectUrl = $this->baseUrl . '?' . http_build_query($params);

        return [
            'success' => true,
            'redirect_url' => $redirectUrl
        ];
    }public function verifyPayment($pid, $rid, $amt, $refId)
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
