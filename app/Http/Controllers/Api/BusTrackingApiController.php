<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusLocation;
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
        
        // Only update if tracking is enabled
        if (!$bus->tracking_enabled) {
            return response()->json([
                'error' => 'Tracking is disabled for this bus',
                'success' => false
            ]);
        }
        
        // Update the bus location
        $bus->updateLocation(
            $request->latitude,
            $request->longitude,
            $request->speed,
            $request->heading
        );
        
        // Save to history
        $location = new BusLocation([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'recorded_at' => now()
        ]);
        
        $bus->locations()->save($location);
        
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
            'limit' => 'nullable|integer|min:1|max:1000'
        ]);
        
        // Simple API key check
        if ($request->api_key != config('services.tracking.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $limit = $request->input('limit', 100);
        $bus = Bus::findOrFail($id);
        
        $locations = $bus->locations()
            ->orderBy('recorded_at', 'desc')
            ->limit($limit)
            ->get([
                'id', 'latitude', 'longitude', 'speed', 
                'heading', 'recorded_at'
            ]);
            
        return response()->json([
            'success' => true,
            'bus_id' => $bus->id,
            'bus_name' => $bus->bus_name,
            'bus_number' => $bus->bus_number,
            'count' => $locations->count(),
            'locations' => $locations
        ]);
    }
}
