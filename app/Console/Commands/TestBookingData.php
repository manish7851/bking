<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Bus;
use App\Models\Route as TravelRoute;

class TestBookingData extends Command
{
    protected $signature = 'test:booking-data';
    protected $description = 'Test and display booking data for debugging';

    public function handle()
    {
        $this->info('=== Testing Booking Data ===');
        
        // Check total records
        $totalBookings = Booking::count();
        $totalCustomers = Customer::count();
        $totalBuses = Bus::count();
        $totalRoutes = TravelRoute::count();
        
        $this->info("Database counts:");
        $this->info("- Bookings: $totalBookings");
        $this->info("- Customers: $totalCustomers");
        $this->info("- Buses: $totalBuses");
        $this->info("- Routes: $totalRoutes");
        
        if ($totalBookings == 0) {
            $this->warn("No bookings found. Creating sample data...");
            $this->createSampleData();
        } else {
            $this->info("\nRecent bookings:");
            $recentBookings = Booking::orderBy('created_at', 'desc')->take(5)->get();
            
            foreach ($recentBookings as $booking) {
                $this->info("- Booking #{$booking->id}");
                $this->info("  Customer ID: {$booking->customer_id}");
                $this->info("  Payment Status: {$booking->payment_status}");
                $this->info("  Route: {$booking->source} -> {$booking->destination}");
                $this->info("  Seat: {$booking->seat}");
                $this->info("  Price: Rs. {$booking->price}");
                $this->info("  Created: {$booking->created_at}");
                $this->info("  ---");
            }
            
            // Test customer-specific bookings
            $this->info("\nTesting customer bookings:");
            $customers = Customer::whereIn('id', $recentBookings->pluck('customer_id')->unique())->get();
            
            foreach ($customers as $customer) {
                $completedBookings = Booking::where('customer_id', $customer->id)
                    ->where('payment_status', 'completed')
                    ->count();
                $this->info("Customer {$customer->id} ({$customer->customer_name}): {$completedBookings} completed bookings");
            }
        }
    }
    
    private function createSampleData()
    {
        // Create sample customer if none exists
        $customer = Customer::first();
        if (!$customer) {
            $customer = Customer::create([
                'customer_name' => 'Test Customer',
                'email' => 'test@example.com',
                'phone' => '9841234567',
                'customer_address' => 'Test Address',
                'password' => bcrypt('password123')
            ]);
            $this->info("Created sample customer: {$customer->customer_name}");
        }
        
        // Create sample bus if none exists
        $bus = Bus::first();
        if (!$bus) {
            $bus = Bus::create([
                'bus_name' => 'Sample Bus',
                'bus_number' => 'BA-1-KHA-1234',
                'seats' => 40,
                'route' => 'Kathmandu-Pokhara'
            ]);
            $this->info("Created sample bus: {$bus->bus_name}");
        }
        
        // Create sample route if none exists
        $route = TravelRoute::first();
        if (!$route) {
            $route = TravelRoute::create([
                'source' => 'Kathmandu',
                'destination' => 'Pokhara',
                'price' => 800.00,
                'bus_id' => $bus->id
            ]);
            $this->info("Created sample route: {$route->source} -> {$route->destination}");
        }
        
        // Create sample booking
        $booking = Booking::create([
            'customer_id' => $customer->id,
            'bus_name' => $bus->bus_name,
            'bus_number' => $bus->bus_number,
            'source' => $route->source,
            'destination' => $route->destination,
            'seat' => 'A1',
            'price' => $route->price,
            'payment_status' => 'completed',
            'payment_method' => 'esewa',
            'payment_details' => json_encode(['test' => true])
        ]);
        
        $this->info("Created sample booking: #{$booking->id}");
        $this->info("Sample data creation completed!");
    }
}
