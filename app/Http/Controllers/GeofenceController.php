<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Geofence;
use App\Models\GeofenceEvent;

class GeofenceController extends Controller
{
    // List all geofences (API & Admin UI)
    public function index(Request $request)
    {
        $geofences = Geofence::all();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $geofences], 200);
        }

        return view('geofences.index', compact('geofences'));
    }

    // Store a new geofence (API & Admin UI)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'center_lat' => 'required|numeric',
            'center_lng' => 'required|numeric',
            'radius' => 'required|numeric|min:0',
        ]);

        $geofence = Geofence::create($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $geofence], 201);
        }

        return redirect()->route('geofences.index')->with('success', 'Geofence created successfully!');
    }

    // Delete a geofence (Admin UI)
    public function destroy(Request $request, Geofence $geofence)
    {
        $geofence->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Geofence deleted successfully!'], 200);
        }

        return redirect()->route('geofences.index')->with('success', 'Geofence deleted successfully!');
    }

    // List geofence events for a bus (API)
    public function busEvents(Request $request, int $busId)
    {
        $events = GeofenceEvent::where('bus_id', $busId)
            ->orderBy('event_time', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $events], 200);
    }

    // Log a geofence event (API)
    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'geofence_id' => 'required|exists:geofences,id',
            'event_type' => 'required|in:enter,exit',
            'event_time' => 'required|date',
        ]);

        $event = GeofenceEvent::create($validated);

        return response()->json(['success' => true, 'data' => $event], 201);
    }
}
