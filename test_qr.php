<?php

require_once 'vendor/autoload.php';

use App\Models\Booking;
use App\Services\QRCodeService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing QR Code Generation...\n";

try {
    // Get a completed booking
    $booking = Booking::where('payment_status', 'completed')->first();
    
    if (!$booking) {
        echo "No completed bookings found. Creating a test booking...\n";
        
        // Create a test booking
        $booking = new Booking([
            'customer_name' => 'Test Customer',
            'customer_email' => 'test@example.com',
            'customer_phone' => '9801234567',
            'seat' => 'A1',
            'bus_name' => 'Test Bus',
            'bus_number' => 'BA 1 PA 1234',
            'source' => 'Kathmandu',
            'destination' => 'Pokhara',
            'price' => 1500,
            'payment_status' => 'completed',
            'payment_method' => 'test'
        ]);
        $booking->save();
    }
    
    echo "Using booking ID: {$booking->id}\n";
    echo "Customer: {$booking->customer_name}\n";
    echo "Seat: {$booking->seat}\n";
    echo "Route: {$booking->source} to {$booking->destination}\n";
    
    // Test QR code generation
    $qrService = new QRCodeService();
    $result = $qrService->generateBookingQRCode($booking);
    
    if ($result['success']) {
        echo "\n✅ QR Code generated successfully!\n";
        echo "QR Code Path: {$result['qr_code_path']}\n";
        echo "QR Code URL: {$result['qr_code_url']}\n";
        echo "Verification Code: {$result['verification_code']}\n";
        
        // Test verification
        echo "\nTesting QR verification...\n";
        $verificationResult = $qrService->verifyBooking($result['verification_code']);
        
        if ($verificationResult['success']) {
            echo "✅ QR Verification successful!\n";
            echo "Verified booking for: {$verificationResult['booking']->customer_name}\n";
        } else {
            echo "❌ QR Verification failed: {$verificationResult['message']}\n";
        }
        
    } else {
        echo "❌ QR Code generation failed: {$result['message']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
