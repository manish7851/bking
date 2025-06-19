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
            return response()->json(['data' => $geofences], 200);
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
            return response()->json(['data' => $geofence], 201);
        }

        return redirect()->route('geofences.index')->with('success', 'Geofence created successfully!');
    }

    // Delete a geofence (Admin UI)
    public function destroy(Geofence $geofence)
    {
        $geofence->delete();

        return redirect()->route('geofences.index')->with('success', 'Geofence deleted successfully!');
    }

    // List geofence events for a bus (API)
    public function busEvents(int $busId)
    {
        $events = GeofenceEvent::where('bus_id', $busId)
            ->orderBy('event_time', 'desc')
            ->get();

        return response()->json(['data' => $events], 200);
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

        return response()->json(['data' => $event], 201);
    }
}
