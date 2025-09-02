<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertApiController extends Controller
{
    /**
     * Get recent alerts (Web + JSON)
     */
    public function getAlerts(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:100',
            'bus_id' => 'nullable|integer|exists:buses,id',
            'type' => 'nullable|string',
            'severity' => 'nullable|string'
        ]);

        // If API key provided, check it
        if ($request->has('api_key') && $request->api_key != config('services.tracking.api_key')) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            } else {
                abort(401, 'Unauthorized');
            }
        }

        $query = Alert::query()->orderBy('created_at', 'desc');

        if ($request->has('bus_id')) $query->where('bus_id', $request->bus_id);
        if ($request->has('type')) $query->where('type', $request->type);
        if ($request->has('severity')) $query->where('severity', $request->severity);

        $limit = $request->input('limit', 10);
        $alerts = $query->limit($limit)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $alerts->count(),
                'alerts' => $alerts
            ]);
        }

        // Web view
        return view('alerts.index', compact('alerts'));
    }

    /**
     * Create a new alert (Web + JSON)
     */
    public function createAlert(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable|string',
            'bus_id' => 'required|integer|exists:buses,id',
            'type' => 'required|string|max:50',
            'message' => 'required|string|max:255',
            'data' => 'nullable|array',
            'severity' => 'required|string|in:info,warning,critical',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        if ($request->has('api_key') && $request->api_key != config('services.tracking.api_key')) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            } else {
                abort(401, 'Unauthorized');
            }
        }

        $alert = Alert::create([
            'bus_id' => $request->bus_id,
            'type' => $request->type,
            'message' => $request->message,
            'data' => $request->data,
            'severity' => $request->severity,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'alert_id' => $alert->id,
                'message' => 'Alert created successfully'
            ]);
        }

        // Web redirect with success
        return redirect()->route('alerts.index')
                         ->with('success', 'Alert created successfully');
    }
}
