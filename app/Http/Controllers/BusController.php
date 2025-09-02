<?php

namespace App\Http\Controllers;

use App\Models\Bus;
use App\Models\Geofence;
use App\Models\GeofenceEvent;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BusController extends Controller
{
    // Store bus
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_name' => 'required|string|max:255',
            'bus_number' => 'required|string|max:255',
            'imei' => 'nullable|string|max:32',
        ]);

        $bus = Bus::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bus' => $bus, 'message' => 'Bus added successfully!']);
        }

        return redirect()->route('buses.index')->with('success', 'Bus added successfully!');
    }

    // List buses
    public function index(Request $request)
    {
        $buses = Bus::all();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'buses' => $buses]);
        }

        return view('buses.index', compact('buses'));
    }

    // Edit bus
    public function edit(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bus' => $bus]);
        }

        return view('buses.edit', compact('bus'));
    }

    // Update bus
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'bus_name' => 'required|string|max:255',
            'bus_number' => 'required|string|max:255',
            'imei' => 'nullable|string|max:32',
        ]);

        $bus = Bus::findOrFail($id);
        $bus->update(array_merge($validated, [
            'tracking_enabled' => $request->has('tracking_enabled')
        ]));

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bus' => $bus, 'message' => 'Bus updated successfully!']);
        }

        return redirect()->route('buses.index')->with('success', 'Bus updated successfully!');
    }

    // Delete bus
    public function destroy(Request $request, $id)
    {
        try {
            $bus = Bus::findOrFail($id);
            $bus->delete();

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Bus deleted successfully.']);
            }

            return redirect()->route('buses.index')->with('success', 'Bus deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Bus delete error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete bus.'], 500);
            }

            return redirect()->route('buses.index')->with('error', 'Failed to delete bus.');
        }
    }

    // Toggle tracking
    public function toggleTracking(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);
        $bus->tracking_enabled = !$bus->tracking_enabled;
        $bus->save();

        $message = $bus->tracking_enabled ? 'Tracking started for bus!' : 'Tracking stopped for bus!';

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bus' => $bus, 'message' => $message]);
        }

        return redirect()->route('buses.index')->with('success', $message);
    }

    // Track single bus (view)
    public function trackBus(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bus' => $bus]);
        }

        return view('buses.track', compact('bus'));
    }

    // Track all buses (view)
    public function trackAllBuses(Request $request)
    {
        $buses = Bus::where('tracking_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'buses' => $buses]);
        }

        return view('buses.track-all', compact('buses'));
    }

    // Get bus location
    public function getLocation(Request $request, $id)
    {
        $bus = Bus::findOrFail($id);

        return response()->json([
            'success' => true,
            'bus' => [
                'id' => $bus->id,
                'bus_name' => $bus->bus_name,
                'latitude' => $bus->latitude,
                'longitude' => $bus->longitude,
                'speed' => $bus->speed,
                'heading' => $bus->heading,
                'status' => $bus->status,
                'last_tracked_at' => $bus->last_tracked_at,
                'updated_at' => $bus->updated_at
            ]
        ]);
    }

    // Location history
    public function locationHistory(Request $request, $id)
    {
        $limit = $request->input('limit', 100);

        $locations = Bus::findOrFail($id)
            ->locations()
            ->orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get(['latitude', 'longitude', 'speed', 'heading', 'recorded_at']);

        return response()->json(['success' => true, 'locations' => $locations]);
    }

    // Live buses
    public function getLiveBuses(Request $request)
    {
        $buses = Bus::where('tracking_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get([
                'id', 'bus_name', 'bus_number', 'latitude', 'longitude', 
                'speed', 'heading', 'status', 'last_tracked_at'
            ]);

        return response()->json(['success' => true, 'buses' => $buses]);
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

        $bus->updateLocation(
            $request->latitude,
            $request->longitude,
            $request->speed,
            $request->heading
        );

        $bus->locations()->create([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'recorded_at' => now()
        ]);

        $this->processGeofenceEvents($bus, $request->latitude, $request->longitude, $request->speed);
        $this->generateAlerts($bus, $request->latitude, $request->longitude, $request->speed);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Bus location updated', 'bus' => $bus]);
        }

        return redirect()->back()->with('success', 'Bus location updated!');
    }

    // GPS API
    public function updateLocationFromGPS(Request $request)
    {
        if ($request->api_key !== 'public_api_key_for_location_updates') {
            return response()->json(['success' => false, 'message' => 'Invalid API key'], 401);
        }

        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
        ]);

        $bus = Bus::findOrFail($request->bus_id);

        $bus->updateLocation(
            $request->lat,
            $request->lng,
            $request->speed ?? 0,
            $request->heading ?? 0
        );

        $bus->locations()->create([
            'latitude' => $request->lat,
            'longitude' => $request->lng,
            'speed' => $request->speed ?? 0,
            'heading' => $request->heading ?? 0,
            'recorded_at' => now()
        ]);

        $this->processGeofenceEvents($bus, $request->lat, $request->lng, $request->speed ?? 0);
        $this->generateAlerts($bus, $request->lat, $request->lng, $request->speed ?? 0);

        return response()->json([
            'success' => true,
            'message' => 'Bus location updated successfully',
            'bus' => $bus
        ]);
    }

    // Geofence events
    private function processGeofenceEvents(Bus $bus, $lat, $lng, $speed)
    {
        $geofences = Geofence::all();
        foreach ($geofences as $geofence) {
            $isInside = $geofence->containsPoint($lat, $lng);
            $previousEvent = GeofenceEvent::where('bus_id', $bus->id)
                ->where('geofence_id', $geofence->id)
                ->orderByDesc('event_time')->first();

            $createEvent = false; $eventType = '';
            if ($isInside && (!$previousEvent || $previousEvent->event_type === 'exit')) {
                $createEvent = true; $eventType = 'enter';
            } elseif (!$isInside && $previousEvent && $previousEvent->event_type === 'enter') {
                $createEvent = true; $eventType = 'exit';
            }

            if ($createEvent) {
                GeofenceEvent::create([
                    'bus_id' => $bus->id,
                    'geofence_id' => $geofence->id,
                    'event_type' => $eventType,
                    'event_time' => now(),
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'speed' => $speed
                ]);

                Alert::create([
                    'bus_id' => $bus->id,
                    'type' => 'geofence',
                    'message' => "Bus {$bus->bus_name} ({$bus->bus_number}) " . ($eventType==='enter'?'entered ':'exited ') . "geofence {$geofence->name}",
                    'data' => ['geofence_id'=>$geofence->id,'event_type'=>$eventType],
                    'severity'=>'info','latitude'=>$lat,'longitude'=>$lng
                ]);
            }
        }
    }

    private function generateAlerts(Bus $bus, $lat, $lng, $speed)
    {
        if ($speed > 80) {
            Alert::create([
                'bus_id' => $bus->id,
                'type' => 'overspeed',
                'message' => "Bus {$bus->bus_name} ({$bus->bus_number}) is overspeeding at {$speed} km/h",
                'data' => ['speed'=>$speed,'threshold'=>80],
                'severity'=>'warning','latitude'=>$lat,'longitude'=>$lng
            ]);
        }

        if ($speed < 2 && $bus->status==='stopped') {
            $idleThreshold = 15;
            if ($bus->last_tracked_at && $bus->last_tracked_at->diffInMinutes(now()) >= $idleThreshold) {
                Alert::create([
                    'bus_id' => $bus->id,
                    'type'=>'idle',
                    'message'=>"Bus {$bus->bus_name} ({$bus->bus_number}) idle for {$idleThreshold}+ min",
                    'data'=>['idle_since'=>$bus->last_tracked_at->toIso8601String(),'idle_minutes'=>$bus->last_tracked_at->diffInMinutes(now())],
                    'severity'=>'info','latitude'=>$lat,'longitude'=>$lng
                ]);
            }
        }
    }

public function getBuses(Request $request, $routeId)
{
    $date = $request->query('date'); // Ensure it's coming from ?date=YYYY-MM-DD

    if (!$date) {
        return response()->json([
            'success' => false,
            'message' => 'Date parameter is required'
        ], 400);
    }

    $buses = Bus::join('routes', 'buses.route_id', '=', 'routes.id')
                ->where('buses.route_id', $routeId)
                ->whereDate('routes.trip_date', $date)
                ->get([
                    'buses.id',
                    'buses.bus_name',
                    'buses.bus_number',
                    'buses.total_seats',
                    'buses.status',
                    'routes.trip_date'
                ]);

    return response()->json([
        'success' => true,
        'data' => $buses,
    ]);


        dd('Fetched Buses:', $buses); // This will show you what buses were found

}

}
