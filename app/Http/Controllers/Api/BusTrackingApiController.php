<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\BusLocation;
use App\Models\BusTracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BusTrackingApiController extends Controller
{
    public function startTracking(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
        ]);

        $bus = Bus::findOrFail($request->bus_id);
        $bus->enableTracking();

        $tracking = BusTracking::create([
            'bus_id' => $bus->id,
            'started_at' => now(),
        ]);
        $bus->current_tracking_id = $tracking->id;
        $bus->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'tracking_id' => $tracking->id, 'bus_id' => $bus->id]);
        }

        return redirect()->back()->with('success', 'Tracking started for bus.');
    }
    public function updateLocation(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'api_key' => 'nullable|string'
        ]);

        // API key check only for JSON requests
        if ($request->has('api_key') && $request->api_key != config('services.tracking.api_key') && $request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $bus = Bus::findOrFail($request->bus_id);

        if (!$bus->tracking_enabled) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'error' => 'Tracking is disabled for this bus']);
            }
            return back()->with('error', 'Tracking is disabled for this bus');
        }

        // Update bus location
        $bus->updateLocation(
            $request->latitude,
            $request->longitude,
            $request->speed,
            $request->heading
        );

        $data = [
            'id' => $bus->id,
            'bus_name' => $bus->bus_name,
            'bus_number' => $bus->bus_number,
            'latitude' => $bus->latitude,
            'longitude' => $bus->longitude,
            'speed' => $bus->speed,
            'heading' => $bus->heading,
            'status' => $bus->status,
            'last_tracked_at' => $bus->last_tracked_at
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bus' => $data]);
        }

        return redirect()->route('buses.track', ['id' => $bus->id])
                         ->with('success', 'Location updated successfully');
    }

    public function getBuses(Request $request)
    {
        $buses = Bus::where('tracking_enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id','bus_name','bus_number','latitude','longitude','speed','heading','status','last_tracked_at']);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => $buses->count(), 'buses' => $buses]);
        }

        return view('buses.index', compact('buses'));
    }

    public function getLocationHistory(Request $request, $id)
    {
        $limit = $request->input('limit', 100);
        $busTracking = BusTracking::where('bus_id', $id)->latest('started_at')->first();
        $locations = $busTracking ? $busTracking->locations()->orderByDesc('recorded_at')->limit($limit)->get() : collect();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'bus_id' => $id, 'count' => $locations->count(), 'locations' => $locations]);
        }

        return view('buses.locations', compact('locations', 'id'));
    }

    public function endTracking(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'tracking_id' => 'required|exists:bus_trackings,id',
        ]);

        $bus = Bus::findOrFail($request->bus_id);
        $tracking = BusTracking::where('id', $request->tracking_id)
                               ->where('bus_id', $bus->id)
                               ->whereNull('ended_at')
                               ->firstOrFail();

        $bus->disableTracking();
        $tracking->update(['ended_at' => now()]);

        // Clear current_tracking_id from the bus
        $bus->current_tracking_id = null;
        $bus->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Tracking ended successfully.']);
        }

        return redirect()->back()->with('success', 'Tracking ended for bus.');
    }
}
