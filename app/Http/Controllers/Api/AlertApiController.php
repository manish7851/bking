<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertApiController extends Controller
{
    /**
     * Get recent alerts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getAlerts(Request $request)
    {
        $request->validate([
            'api_key' => 'required',
            'limit' => 'nullable|integer|min:1|max:100',
            'bus_id' => 'nullable|integer|exists:buses,id',
            'type' => 'nullable|string',
            'severity' => 'nullable|string'
        ]);
        
        // Simple API key check
        if ($request->api_key != config('services.tracking.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $query = Alert::query()->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($request->has('bus_id')) {
            $query->where('bus_id', $request->bus_id);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }
        
        // Apply limit
        $limit = $request->input('limit', 10);
        $alerts = $query->limit($limit)->get();
        
        return response()->json([
            'success' => true,
            'count' => $alerts->count(),
            'alerts' => $alerts
        ]);
    }
    
    /**
     * Create a new alert
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createAlert(Request $request)
    {
        $request->validate([
            'api_key' => 'required',
            'bus_id' => 'required|integer|exists:buses,id',
            'type' => 'required|string|max:50',
            'message' => 'required|string|max:255',
            'data' => 'nullable|array',
            'severity' => 'required|string|in:info,warning,critical',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);
        
        // Simple API key check
        if ($request->api_key != config('services.tracking.api_key')) {
            return response()->json(['error' => 'Unauthorized'], 401);
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
        
        return response()->json([
            'success' => true,
            'alert_id' => $alert->id,
            'message' => 'Alert created successfully'
        ]);
    }
}
