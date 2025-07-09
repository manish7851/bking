<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Alert;
use App\Models\Route;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $customerId = session('customer_id');
        $user = \App\Models\Customer::find($customerId);
        $email = $user->email ?? null;
        if (!$email) {
            $subscriptions = Subscription::with('alert')->get();
        } else {
            $subscriptions = Subscription::where('email', $email)->with('alert')->get();
        }
        return view('subscriptions.index', compact('subscriptions'));
    }

    public function create(Request $request)
    {
        $activeRoute = null;
        if ($request->has('active_route_id')) {
            $activeRoute = Route::with('bus')->find($request->input('active_route_id'));
        }
        $routes = Route::with('bus')->get();
        return view('subscriptions.create', compact('routes', 'activeRoute'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'isadmin' => 'required|boolean',
            'email' => 'required|email',
            'route_id' => 'required|exists:routes,id',
            'delivered' => 'required|boolean',
        ]);

        $route = \App\Models\Route::with('bus')->findOrFail($request->route_id);
        $alerts = [];

        // Source alert
        if ($request->has('alert_source')) {
            $alerts[] = $this->findOrCreateAlert($route->bus_id, $route->id, 'geofence_exit', $request->message, $route->source_latitude, $route->source_longitude);
        }
        // Destination alert
        if ($request->has('alert_destination')) {
            $alerts[] = $this->findOrCreateAlert($route->bus_id, $route->id, 'geofence_entry', $request->message, $route->destination_latitude, $route->destination_longitude);
        }
        // Alert zone
        if ($request->has('alert_zone') && $request->zone_latitude && $request->zone_longitude) {
            $alerts[] = $this->findOrCreateAlert($route->bus_id, $route->id, 'geofence_entry', $request->message, $request->zone_latitude, $request->zone_longitude);
        }

        // Create a subscription for each alert
        foreach ($alerts as $alert) {
            Subscription::create([
                'isadmin' => $validated['isadmin'],
                'email' => $validated['email'],
                'alert_id' => $alert->id,
                'delivered' => $validated['delivered'],
            ]);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription(s) created successfully.');
    }

    private function findOrCreateAlert($bus_id, $route_id, $type, $message, $latitude, $longitude)
    {
        $alert = Alert::where('bus_id', $bus_id)
            ->where('type', $type)
            ->where('latitude', $latitude)
            ->where('longitude', $longitude)
            ->first();
        if ($alert) return $alert;
        return Alert::create([
            'bus_id' => $bus_id,
            'type' => $type,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'message' => $message,
            'severity' => 'info',
            'is_read' => false,
        ]);
    }

    public function show(Subscription $subscription)
    {
        return view('subscriptions.show', compact('subscription'));
    }

    public function edit(Subscription $subscription)
    {
        $alerts = Alert::all();
        return view('subscriptions.edit', compact('subscription', 'alerts'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        // Delete the old subscription
        $subscription->delete();

        $validated = $request->validate([
            'isadmin' => 'required|boolean',
            'email' => 'required|email',
            'route_id' => 'required|exists:routes,id',
            'delivered' => 'required|boolean',
        ]);

        $route = \App\Models\Route::with('bus')->findOrFail($request->route_id);
        $alerts = [];

        if ($request->has('alert_source')) {
            $alerts[] = $this->findOrCreateAlert($route->bus_id, $route->id, 'geofence_exit', $route->source_latitude, $route->source_longitude);
        }
        if ($request->has('alert_destination')) {
            $alerts[] = $this->findOrCreateAlert($route->bus_id, $route->id, 'geofence_entry', $route->destination_latitude, $route->destination_longitude);
        }
        if ($request->has('alert_zone') && $request->zone_latitude && $request->zone_longitude) {
            $alerts[] = $this->findOrCreateAlert($route->bus_id, $route->id, 'geofence_entry', $request->zone_latitude, $request->zone_longitude);
        }

        foreach ($alerts as $alert) {
            Subscription::create([
                'isadmin' => $validated['isadmin'],
                'email' => $validated['email'],
                'alert_id' => $alert->id,
                'delivered' => $validated['delivered'],
            ]);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription(s) updated successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('subscriptions.index')->with('success', 'Subscription deleted successfully.');
    }
}
