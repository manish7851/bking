<?php

// App/Http/Controllers/BusController.php

namespace App\Http\Controllers;

use App\Models\Bus;
use Illuminate\Http\Request;

class BusController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_name' => 'required|string|max:255',
            'bus_number' => 'required|string|max:255',
            'imei' => 'nullable|string|max:32',
        ]);
    
        \App\Models\Bus::create($validated);
        return redirect()->route('buses.index')->with('success', 'Bus added successfully!');
    }
    
    // Display the list of buses
    public function index()
    {
        $buses = Bus::all();
        return view('buses.index', compact('buses'));
    }

    // Show the form for editing a specific bus
    public function edit($id)
    {
        $bus = Bus::findOrFail($id);
        return view('buses.edit', compact('bus'));
    }
    
    // Handle the update of a bus
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'bus_name' => 'required|string|max:255',
            'bus_number' => 'required|string|max:255',
            'imei' => 'nullable|string|max:32',
        ]);

        $bus = Bus::findOrFail($id);
        $bus->bus_name = $request->input('bus_name');
        $bus->bus_number = $request->input('bus_number');
        $bus->imei = $request->input('imei');
        $bus->tracking_enabled = $request->has('tracking_enabled') ? true : false;
        $bus->save();

        return redirect()->route('buses.index')->with('success', 'Bus updated successfully!');
    }
    
    // Delete a bus
    public function destroy($id)
    {
        try {
            $bus = Bus::findOrFail($id);
            $bus->delete();
            return redirect()->route('buses.index')->with('success', 'Bus deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('buses.index')->with('error', 'Failed to delete bus: ' . $e->getMessage());
        }
    }
    
    // Start tracking a bus
    public function startTracking($id)
    {
        $bus = Bus::findOrFail($id);
        $bus->startTracking();
        return redirect()->route('buses.index')->with('success', 'Tracking started for bus!');
    }

    // Stop tracking a bus
    public function stopTracking($id)
    {
        $bus = Bus::findOrFail($id);
        $bus->stopTracking();
        return redirect()->route('buses.index')->with('success', 'Tracking stopped for bus!');
    }
    
    // Toggle bus tracking status
    public function toggleTracking($id)
    {
        $bus = Bus::findOrFail($id);
        $bus->tracking_enabled = !$bus->tracking_enabled;
        $bus->save();

        $message = $bus->tracking_enabled ? 'Tracking started for bus!' : 'Tracking stopped for bus!';
        return redirect()->route('buses.index')->with('success', $message);
    }

    // Display single bus on map
    public function trackBus($id)
    {
        $bus = Bus::findOrFail($id);
        return view('buses.track', compact('bus'));
    }

    // Get location history for a specific bus
    public function getLocations($id)
    {
        $bus = Bus::findOrFail($id);
        $locations = $bus->locations()
            ->orderBy('recorded_at', 'desc')
            ->limit(100)
            ->get(['latitude', 'longitude', 'speed', 'heading', 'recorded_at']);
        
        return response()->json([
            'bus' => [
                'id' => $bus->id,
                'bus_name' => $bus->bus_name,
                'bus_number' => $bus->bus_number,
                'latitude' => $bus->latitude,
                'longitude' => $bus->longitude,
                'speed' => $bus->speed,
                'heading' => $bus->heading,
                'last_tracked_at' => $bus->last_tracked_at
            ],
            'locations' => $locations
        ]);
    }

    // Display all buses on a single map
    public function trackAllBuses()
    {
        $buses = Bus::where('tracking_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        
        return view('buses.track-all', compact('buses'));
    }
    
    // Update bus location manually
    public function updateLocation(Request $request, $id)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
        ]);
        
        $bus = Bus::findOrFail($id);
        if ($bus instanceof \App\Models\Bus) {
            $bus->updateLocation(
                $request->latitude,
                $request->longitude,
                $request->speed,
                $request->heading
            );
            
            // Save to bus_locations table for history
            $bus->locations()->create([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'speed' => $request->speed,
                'heading' => $request->heading,
                'recorded_at' => now()
            ]);
        }
        
        // Process geofence events
        $this->processGeofenceEvents($bus, $request->latitude, $request->longitude, $request->speed);
        
        // Generate alerts if necessary (overspeed, etc.)
        $this->generateAlerts($bus, $request->latitude, $request->longitude, $request->speed);
        
        return redirect()->back()->with('success', 'Bus location updated!');
    }
    
    // API endpoint to get bus location
    public function getLocation($id)
    {
        $bus = Bus::findOrFail($id);
        return response()->json([
            'id' => $bus->id,
            'latitude' => $bus->latitude,
            'longitude' => $bus->longitude,
            'speed' => $bus->speed,
            'heading' => $bus->heading,
            'status' => $bus->status,
            'last_tracked_at' => $bus->last_tracked_at,
            'updated_at' => $bus->updated_at
        ]);
    }
    
    // API endpoint for location history
    public function locationHistory(Request $request, $id)
    {
        $limit = $request->input('limit', 100);
        
        $locations = Bus::findOrFail($id)
            ->locations()
            ->orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get(['latitude', 'longitude', 'speed', 'heading', 'recorded_at']);
            
        return response()->json($locations);
    }
    
    // API endpoint for all active buses
    public function getLiveBuses()
    {
        $buses = Bus::where('tracking_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get([
                'id', 'bus_name', 'bus_number', 'latitude', 'longitude', 
                'speed', 'heading', 'status', 'last_tracked_at'
            ]);
            
        return response()->json($buses);
    }
    
    // Show a bus on the map
    public function showMap($id)
    {
        $bus = Bus::findOrFail($id);
        return view('buses.map', compact('bus'));
    }
    
    // Display the map view
    public function map()
    {
        // Optionally, fetch buses if you want to show them on the map
        // $buses = Bus::all();
        // return view('buses.map', compact('buses'));
        return view('buses.map');
    }
    
    // Process potential geofence events
    private function processGeofenceEvents(Bus $bus, $latitude, $longitude, $speed)
    {
        $geofences = \App\Models\Geofence::all();
        
        foreach ($geofences as $geofence) {
            $isInside = $geofence->containsPoint($latitude, $longitude);
            
            // Check if there's a previous event
            $previousEvent = \App\Models\GeofenceEvent::where('bus_id', $bus->id)
                ->where('geofence_id', $geofence->id)
                ->orderByDesc('event_time')
                ->first();
            
            // Determine if we need to create an event
            $createEvent = false;
            $eventType = '';
            
            if ($isInside && (!$previousEvent || $previousEvent->event_type === 'exit')) {
                $createEvent = true;
                $eventType = 'enter';
            } elseif (!$isInside && $previousEvent && $previousEvent->event_type === 'enter') {
                $createEvent = true;
                $eventType = 'exit';
            }
            
            if ($createEvent) {
                \App\Models\GeofenceEvent::create([
                    'bus_id' => $bus->id,
                    'geofence_id' => $geofence->id,
                    'event_type' => $eventType,
                    'event_time' => now(),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'speed' => $speed
                ]);
                
                // Create an alert for this geofence event
                \App\Models\Alert::create([
                    'bus_id' => $bus->id,
                    'type' => 'geofence',
                    'message' => "Bus {$bus->bus_name} ({$bus->bus_number}) " . 
                                 ($eventType === 'enter' ? 'entered ' : 'exited ') . 
                                 "geofence {$geofence->name}",
                    'data' => [
                        'geofence_id' => $geofence->id,
                        'geofence_name' => $geofence->name,
                        'event_type' => $eventType
                    ],
                    'severity' => 'info',
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
            }
        }
    }
    
    // Generate alerts based on bus data
    private function generateAlerts(Bus $bus, $latitude, $longitude, $speed)
    {
        // Check for overspeed
        if ($speed > 80) { // 80 km/h threshold, adjust as needed
            \App\Models\Alert::create([
                'bus_id' => $bus->id,
                'type' => 'overspeed',
                'message' => "Bus {$bus->bus_name} ({$bus->bus_number}) is overspeeding at {$speed} km/h",
                'data' => [
                    'speed' => $speed,
                    'threshold' => 80
                ],
                'severity' => 'warning',
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);
        }
        
        // Check for extended idle time
        if ($speed < 2 && $bus->status === 'stopped') {
            $idleThreshold = 15; // minutes
            
            if ($bus->last_tracked_at && 
                $bus->last_tracked_at->diffInMinutes(now()) >= $idleThreshold) {
                
                \App\Models\Alert::create([
                    'bus_id' => $bus->id,
                    'type' => 'idle',
                    'message' => "Bus {$bus->bus_name} ({$bus->bus_number}) has been idle for {$idleThreshold}+ minutes",
                    'data' => [
                        'idle_since' => $bus->last_tracked_at->toIso8601String(),
                        'idle_minutes' => $bus->last_tracked_at->diffInMinutes(now())
                    ],
                    'severity' => 'info',
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
            }
        }
    }
    
    // API endpoint to update bus location from GPS device
    public function updateLocationFromGPS(Request $request)
    {
        // Validate API key
        if ($request->api_key !== 'public_api_key_for_location_updates') { 
            return response()->json(['error' => 'Invalid API key'], 401);
        }
        
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
        ]);
        
        $bus = Bus::findOrFail($request->bus_id);
        if (!($bus instanceof \App\Models\Bus)) {
            return response()->json(['error' => 'Bus not found'], 404);
        }
        
        // Update bus location
        $bus->updateLocation(
            $request->lat,
            $request->lng,
            $request->speed ?? 0,
            $request->heading ?? 0
        );
        
        // Save to bus_locations table for history
        $bus->locations()->create([
            'latitude' => $request->lat,
            'longitude' => $request->lng,
            'speed' => $request->speed ?? 0,
            'heading' => $request->heading ?? 0,
            'recorded_at' => now()
        ]);
        
        // Process geofence events
        $this->processGeofenceEvents($bus, $request->lat, $request->lng, $request->speed ?? 0);
        
        // Generate alerts if necessary
        $this->generateAlerts($bus, $request->lat, $request->lng, $request->speed ?? 0);
        
        return response()->json([
            'success' => true,
            'message' => 'Bus location updated successfully',
            'bus' => [
                'id' => $bus->id,
                'bus_name' => $bus->bus_name,
                'latitude' => $bus->latitude,
                'longitude' => $bus->longitude,
                'updated_at' => $bus->updated_at
            ]
        ]);
    }

    // Get route coordinates for a bus
    public function getRouteCoordinates($id)
    {
        $bus = Bus::with('routes')->findOrFail($id);
        $activeRoute = $bus->routes()
            ->whereDate('trip_date', '>=', now())
            ->orderBy('trip_date')
            ->first();

        if (!$activeRoute) {
            return response()->json([
                'error' => 'No active route found for this bus'
            ], 404);
        }

        // Here you would normally get real coordinates from your database
        // For now, we'll use hardcoded coordinates for demonstration
        $coordinates = [
            'source' => [27.7172, 85.3240], // Kathmandu
            'destination' => [28.2096, 83.9856], // Pokhara
            'waypoints' => [
                [27.9512, 84.6355], // Mugling
                [28.0423, 84.4281]  // Damauli
            ]
        ];

        return response()->json([
            'source' => $coordinates['source'],
            'destination' => $coordinates['destination'],
            'waypoints' => $coordinates['waypoints'],
            'route_info' => [
                'source_name' => $activeRoute->source,
                'destination_name' => $activeRoute->destination,
                'trip_date' => $activeRoute->trip_date
            ]
        ]);
    }
    
    // Save custom path (source/destination) for a bus
    public function saveCustomPath(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);
        $request->validate([
            'source' => 'required|array',
            'destination' => 'required|array',
            'source.0' => 'required|numeric',
            'source.1' => 'required|numeric',
            'destination.0' => 'required|numeric',
            'destination.1' => 'required|numeric',
        ]);
        $bus->custom_path_source = json_encode($request->source);
        $bus->custom_path_destination = json_encode($request->destination);
        $bus->save();
        return response()->json(['success' => true]);
    }

    // Fetch custom path for a bus (used by both map and detail views)
    public function getCustomPath($id)
    {
        $bus = Bus::findOrFail($id);
        return response()->json([
            'source' => $bus->custom_path_source ? json_decode($bus->custom_path_source) : null,
            'destination' => $bus->custom_path_destination ? json_decode($bus->custom_path_destination) : null,
        ]);
    }
}

