<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Alert;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    // List subscriptions (Web + JSON)
    public function index(Request $request)
    {
        $customerId = session('customer_id');
        $user = \App\Models\Customer::find($customerId);
        $email = $user->email ?? null;

        $subscriptions = $email
            ? Subscription::where('email', $email)->with('alert')->get()
            : Subscription::where('isAdmin', true)->with('alert')->get();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'subscriptions' => $subscriptions]);
        }

        return view('subscriptions.index', compact('subscriptions'));
    }

    // Show create form (Web only)
    public function create(Request $request)
    {
        $activeRoute = null;
        if ($request->has('active_route_id')) {
            $activeRoute = Route::with('bus')->find($request->input('active_route_id'));
        }
        $routes = Route::with('bus')->get();
        return view('subscriptions.create', compact('routes', 'activeRoute'));
    }

    // Store subscription (Web + JSON)
    public function store(Request $request)
    {
        $customerId = session('customer_id');
        $validated = $request->validate([
            'isadmin' => 'required|boolean',
            'email' => 'required|email',
            'route_id' => 'required|exists:routes,id',
            'delivered' => 'required|boolean',
        ]);

        $route = Route::with('bus')->findOrFail($request->route_id);
        $alerts = [];

        if ($request->has('alert_source')) {
            $alerts[] = $this->findOrCreateAlert(
                $route->bus_id,
                $route->id,
                'geofence_exit',
                $request->message,
                $route->source_latitude,
                $route->source_longitude,
                $route->source
            );
        }

        if ($request->has('alert_destination')) {
            $alerts[] = $this->findOrCreateAlert(
                $route->bus_id,
                $route->id,
                'geofence_entry',
                $request->message,
                $route->destination_latitude,
                $route->destination_longitude,
                $route->destination
            );
        }

        if ($request->has('alert_zone') && $request->zone_latitude && $request->zone_longitude) {
            $alerts[] = $this->findOrCreateAlert(
                $route->bus_id,
                $route->id,
                'geofence_entry',
                $request->message,
                $request->zone_latitude,
                $request->zone_longitude,
                $request->alert_zone_address
            );
        }

        foreach ($alerts as $alert) {
            Subscription::create([
                'isadmin' => $customerId ? false : true,
                'email' => $validated['email'],
                'alert_id' => $alert->id,
                'delivered' => $validated['delivered'],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Subscription(s) created successfully.']);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription(s) created successfully.');
    }

    private function findOrCreateAlert($bus_id, $route_id, $type, $message, $latitude, $longitude, $location_name = null)
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
            'location_name' => $location_name,
            'severity' => 'info',
            'is_read' => false,
        ]);
    }

    // Show subscription (Web + JSON)
    public function show(Request $request, Subscription $subscription)
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'subscription' => $subscription]);
        }
        return view('subscriptions.show', compact('subscription'));
    }

    // Edit subscription (Web only)
    public function edit(Subscription $subscription)
    {
        $alerts = Alert::all();
        return view('subscriptions.edit', compact('subscription', 'alerts'));
    }

    // Update subscription (Web + JSON)
    public function update(Request $request, Subscription $subscription)
    {
        $subscription->delete();

        $validated = $request->validate([
            'isadmin' => 'required|boolean',
            'email' => 'required|email',
            'route_id' => 'required|exists:routes,id',
            'delivered' => 'required|boolean',
        ]);

        $route = Route::with('bus')->findOrFail($request->route_id);
        $alerts = [];

        if ($request->has('alert_source')) {
            $alerts[] = $this->findOrCreateAlert(
                $route->bus_id,
                $route->id,
                'geofence_exit',
                $request->message,
                $route->source_latitude,
                $route->source_longitude,
                $route->source
            );
        }

        if ($request->has('alert_destination')) {
            $alerts[] = $this->findOrCreateAlert(
                $route->bus_id,
                $route->id,
                'geofence_entry',
                $request->message,
                $route->destination_latitude,
                $route->destination_longitude,
                $route->destination
            );
        }

        if ($request->has('alert_zone') && $request->zone_latitude && $request->zone_longitude) {
            $alerts[] = $this->findOrCreateAlert(
                $route->bus_id,
                $route->id,
                'geofence_entry',
                $request->message,
                $request->zone_latitude,
                $request->zone_longitude,
                $request->alert_zone_address
            );
        }

        foreach ($alerts as $alert) {
            Subscription::create([
                'isadmin' => $validated['isadmin'],
                'email' => $validated['email'],
                'alert_id' => $alert->id,
                'delivered' => $validated['delivered'],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Subscription(s) updated successfully.']);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription(s) updated successfully.');
    }

    // Delete subscription (Web + JSON)
    public function destroy(Request $request, Subscription $subscription)
    {
        $subscription->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Subscription deleted successfully.']);
        }

        return redirect()->route('subscriptions.index')->with('success', 'Subscription deleted successfully.');
    }
}
