<?php

// Create test booking for debugging
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Bus;

echo "=== Creating Test Booking Data ===\n\n";

try {
    // Check if customer exists
    $customer = Customer::first();
    if (!$customer) {
        $customer = Customer::create([
            'customer_name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '9841234567',
            'customer_address' => 'Test Address, Kathmandu',
            'password' => bcrypt('password123')
        ]);
        echo "Created test customer: {$customer->customer_name} (ID: {$customer->id})\n";
    } else {
        echo "Using existing customer: {$customer->customer_name} (ID: {$customer->id})\n";
    }
    
    // Check if bus exists
    $bus = Bus::first();
    if (!$bus) {
        $bus = Bus::create([
            'bus_name' => 'Deluxe Express',
            'bus_number' => 'BA-1-KHA-1234',
            'seats' => 40,
            'route' => 'Kathmandu-Pokhara'
        ]);
        echo "Created test bus: {$bus->bus_name} ({$bus->bus_number})\n";
    } else {
        echo "Using existing bus: {$bus->bus_name} ({$bus->bus_number})\n";
    }
    
    // Create test booking
    $booking = Booking::create([
        'customer_id' => $customer->id,
        'bus_name' => $bus->bus_name,
        'bus_number' => $bus->bus_number,
        'source' => 'Kathmandu',
        'destination' => 'Pokhara',
        'seat' => 'A' . rand(1, 15),
        'price' => 800.00,
        'payment_status' => 'completed',
        'payment_method' => 'esewa',
        'payment_details' => json_encode([
            'transaction_id' => 'TEST_' . time(),
            'amount' => 800.00,
            'verification_status' => 'success'
        ])
    ]);
    
    echo "Created test booking: #{$booking->id}\n";
    echo "  Customer: {$booking->customer_id}\n";
    echo "  Route: {$booking->source} -> {$booking->destination}\n";
    echo "  Seat: {$booking->seat}\n";
    echo "  Amount: Rs. {$booking->price}\n";
    echo "  Status: {$booking->payment_status}\n";
    echo "  Created: {$booking->created_at}\n";
    
    // Test dashboard query
    echo "\n=== Testing Dashboard Query ===\n";
    $allBookings = Booking::where('customer_id', $customer->id)
        ->where('payment_status', 'completed')
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Found {$allBookings->count()} completed bookings for customer {$customer->id}\n";
    
    foreach ($allBookings as $b) {
        echo "  - Booking #{$b->id}: {$b->source} -> {$b->destination}, Seat: {$b->seat}, Rs. {$b->price}\n";
    }
    
    echo "\n=== Test URLs ===\n";
    echo "Debug dashboard: http://127.0.0.1:8000/debug-dashboard/{$customer->id}\n";
    echo "Test dashboard: http://127.0.0.1:8000/test-dashboard/{$customer->id}\n";
    
    echo "\n=== Success! ===\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
