<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusLocation;
use App\Models\BusTracking;
use Illuminate\Http\Request;

class BusTrackingApiController extends Controller
{
    /**
     * Update bus location.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'api_key' => 'required'
        ]);

        // Simple API key check - in a real application, you'd use proper API authentication
        // if ($request->api_key != config('services.tracking.api_key')) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }
        
        $bus = Bus::findOrFail($request->bus_id);
        if (!$bus->tracking_enabled) {
            return response()->json([
                'error' => 'Tracking is disabled for this bus',
                'success' => false
            ]);
        }

        // Use current_tracking_id if present, otherwise create a new BusTracking
        if ($bus->current_tracking_id) {
            $busTracking = BusTracking::find($bus->current_tracking_id);
            // If not found (shouldn't happen), fallback to create
            if (!$busTracking) {
                $busTracking = BusTracking::create([
                    'bus_id' => $bus->id,
                    'started_at' => now()
                ]);
                $bus->current_tracking_id = $busTracking->id;
                $bus->save();
            }
        } else {
            $busTracking = BusTracking::create([
                'bus_id' => $bus->id,
                'started_at' => now()
            ]);
            $bus->current_tracking_id = $busTracking->id;
            $bus->save();
        }

        $bus->updateLocation(
            $request->latitude,
            $request->longitude,
            $request->speed,
            $request->heading
        );

        $location = new BusLocation([
            'bus_id' => $bus->id,
            'bus_tracking_id' => $busTracking->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'recorded_at' => now()
        ]);
        $busTracking->locations()->save($location);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'bus' => [
                'id' => $bus->id,
                'bus_name' => $bus->bus_name,
                'bus_number' => $bus->bus_number,
                'latitude' => $bus->latitude,
                'longitude' => $bus->longitude,
                'speed' => $bus->speed,
                'heading' => $bus->heading,
                'status' => $bus->status,
                'last_tracked_at' => $bus->last_tracked_at
            ]
        ]);
    }

    /**
     * Get all active buses with location data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getBuses(Request $request)
    {
        $request->validate([
            'api_key' => 'required'
        ]);
        
        // Simple API key check
        if ($request->api_key != config('services.tracking.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $buses = Bus::where('tracking_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get([
                'id', 'bus_name', 'bus_number', 'latitude', 'longitude', 
                'speed', 'heading', 'status', 'last_tracked_at'
            ]);
            
        return response()->json([
            'success' => true,
            'count' => $buses->count(),
            'buses' => $buses
        ]);
    }

    /**
     * Get location history for a specific bus.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getLocationHistory(Request $request, $id)
    {
        $request->validate([
            'api_key' => 'required',
            'limit' => 'nullable|integer|min:1|max:1000',
            'bus_tracking_id' => 'nullable|exists:bus_trackings,id'
        ]);
        
        // Simple API key check
        if ($request->api_key != config('services.tracking.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $limit = $request->input('limit', 100);
        $busTrackingId = $request->input('bus_tracking_id');
        if ($busTrackingId) {
            $busTracking = BusTracking::where('bus_id', $id)->where('id', $busTrackingId)->firstOrFail();
            $locations = $busTracking->locations()->orderBy('recorded_at', 'desc')->limit($limit)->get([
                'id', 'latitude', 'longitude', 'speed', 'heading', 'recorded_at'
            ]);
        } else {
            $busTrackings = BusTracking::where('bus_id', $id)->pluck('id');
            $locations = BusLocation::whereIn('bus_tracking_id', $busTrackings)
                ->orderBy('recorded_at', 'desc')
                ->limit($limit)
                ->get([
                    'id', 'latitude', 'longitude', 'speed', 'heading', 'recorded_at'
                ]);
        }
        return response()->json([
            'success' => true,
            'bus_id' => $id,
            'count' => $locations->count(),
            'locations' => $locations
        ]);
    }

    /**
     * Start a new tracking session for a bus.
     * Sets tracking_enabled to true, creates a new BusTracking, and updates current_tracking_id.
     */
    public function startTracking(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'api_key' => 'required'
        ]);
        // Simple API key check
        // if ($request->api_key != config('services.tracking.api_key')) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }
        $bus = Bus::findOrFail($request->bus_id);
        if ($bus->current_tracking_id) {
            return response()->json(['error' => 'Tracking already started', 'success' => false], 400);
        }
        $busTracking = BusTracking::create([
            'bus_id' => $bus->id,
            'started_at' => now()
        ]);
        $bus->tracking_enabled = true;
        $bus->current_tracking_id = $busTracking->id;
        $bus->last_tracked_at = now();
        $bus->save();
        return response()->json([
            'success' => true,
            'message' => 'Tracking started',
            'tracking_id' => $busTracking->id
        ]);
    }

    /**
     * End the current tracking session for a bus.
     * Sets ended_at on BusTracking, disables tracking, and clears current_tracking_id.
     */
    public function endTracking(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'api_key' => 'required'
        ]);
        // Simple API key check
        // if ($request->api_key != config('services.tracking.api_key')) {
        //     return response()->json(['error' => 'Unauthorized'], 401);
        // }
        $bus = Bus::findOrFail($request->bus_id);
        if (!$bus->current_tracking_id) {
            return response()->json(['error' => 'No active tracking session', 'success' => false], 400);
        }
        $busTracking = BusTracking::find($bus->current_tracking_id);
        if ($busTracking && !$busTracking->ended_at) {
            $busTracking->ended_at = now();
            $busTracking->save();
        }
        $bus->tracking_enabled = false;
        $bus->current_tracking_id = null;
        $bus->save();
        return response()->json([
            'success' => true,
            'message' => 'Tracking ended'
        ]);
    }
}
