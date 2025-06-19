<?php

namespace App\Console\Commands;

use App\Models\Bus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateBusMovement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bus:simulate {bus_id} {duration=10} {interval=5}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate bus movement for testing the GPS tracking system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $busId = $this->argument('bus_id');
        $duration = $this->argument('duration'); // in minutes
        $interval = $this->argument('interval'); // in seconds
        
        $bus = Bus::find($busId);
        
        if (!$bus) {
            $this->error("Bus with ID {$busId} not found!");
            return 1;
        }
        
        if (!$bus->tracking_enabled) {
            $bus->tracking_enabled = true;
            $bus->save();
            $this->info("Tracking enabled for bus {$bus->bus_name}");
        }
        
        // Set initial position if not set
        if (!$bus->latitude || !$bus->longitude) {
            // Default location (can be customized)
            $bus->latitude = 27.7172;  // Kathmandu
            $bus->longitude = 85.3240;
            $bus->save();
            $this->info("Initial position set for the bus");
        }
        
        $this->info("Starting simulation for bus {$bus->bus_name} ({$bus->bus_number})");
        $this->info("Duration: {$duration} minutes, Interval: {$interval} seconds");
        
        $startTime = now();
        $endTime = $startTime->copy()->addMinutes($duration);
        
        $iterations = 0;
        $apiKey = config('services.tracking.api_key');
        $baseUrl = config('app.url');
        
        while (now()->lt($endTime)) {
            // Generate a small random movement
            $latDelta = mt_rand(-10, 10) / 10000; // Small random delta
            $lngDelta = mt_rand(-10, 10) / 10000;
            $speed = mt_rand(0, 60); // Random speed between 0-60 km/h
            $heading = mt_rand(0, 359); // Random heading 0-359 degrees
            
            $newLat = $bus->latitude + $latDelta;
            $newLng = $bus->longitude + $lngDelta;
            
            $this->info("Updating location: {$newLat}, {$newLng}, Speed: {$speed} km/h");
            
            // Make API call to update the bus location
            try {
                $response = Http::post("{$baseUrl}/api/bus/location/update", [
                    'bus_id' => $bus->id,
                    'latitude' => $newLat,
                    'longitude' => $newLng,
                    'speed' => $speed,
                    'heading' => $heading,
                    'api_key' => $apiKey,
                ]);
                
                if ($response->successful()) {
                    $this->info("Location updated successfully via API");
                } else {
                    $this->error("API update failed: " . $response->body());
                }
            } catch (\Exception $e) {
                $this->error("API call failed: " . $e->getMessage());
                
                // Fallback to direct database update
                $bus->updateLocation($newLat, $newLng, $speed, $heading);
                $this->info("Location updated directly in database");
            }
            
            $iterations++;
            $this->info("Completed iteration {$iterations}, sleeping for {$interval} seconds...");
            
            sleep($interval);
        }
        
        $this->info("Simulation complete! Generated {$iterations} location updates");
        return 0;
    }
}
