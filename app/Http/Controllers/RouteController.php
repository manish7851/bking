<?php

namespace App\Http\Controllers;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\Bus;
use App\Models\Route;
use Illuminate\Support\Facades\Log;

class RouteController extends Controller
{
    // List all routes (Web + JSON)
public function index(Request $request)
{
    $routes = Route::with('bus')->get();
    $buses = Bus::all();

    $data = $routes->map(function ($route) {
        $isToday = $route->trip_date && Carbon::parse($route->trip_date)->isToday();

        return [
            'route_id'    => $route->id,
            'source'      => $route->source,
            'destination' => $route->destination,
            'trip_date'   => $route->trip_date,
            'price'       => $route->price,
            'bus' => [
                'id'          => $route->bus->id ?? null,
                'name'        => $route->bus->bus_name ?? '',   // explicitly pathaune
                'number'      => $route->bus->bus_number ?? '',
                'total_seats' => $route->bus->total_seats ?? 0,
                'status'      => $route->bus->status ?? 'offline',
            ],
        ];
    });

    if ($request->wantsJson()) {
        return response()->json([
            'success' => true,
            'routes'  => $data
        ]);
    }

  return view('routes.routes', compact('routes','buses'));

}

    // Show a single route
    public function edit(Request $request, $id)
    {
        $route = Route::findOrFail($id);
        $buses = Bus::all();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'route' => $route,
                'buses' => $buses
            ]);
        }

        return view('routes.edit', compact('route', 'buses'));
    }

    // Create a new route
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'source' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric',
            'trip_date' => 'required|date_format:Y-m-d\TH:i|after_or_equal:today',
        ]);

        $route = Route::create([
            'bus_id' => $validated['bus_id'],
            'source' => $validated['source'],
            'destination' => $validated['destination'],
            'source_latitude' => $request->input('source_latitude'),
            'source_longitude' => $request->input('source_longitude'),
            'destination_latitude' => $request->input('destination_latitude'),
            'destination_longitude' => $request->input('destination_longitude'),
            'price' => $validated['price'],
            'trip_date' => $validated['trip_date'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'route' => $route], 201);
        }

        return redirect()->back()->with('success', 'Route created successfully!');
    }

    // Update a route
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric',
            'trip_date' => 'required|date_format:Y-m-d',
        ]);

        $route = Route::findOrFail($id);
        $route->update([
            'source' => $validated['source'],
            'destination' => $validated['destination'],
            'source_latitude' => $request->input('source_latitude'),
            'source_longitude' => $request->input('source_longitude'),
            'destination_latitude' => $request->input('destination_latitude'),
            'destination_longitude' => $request->input('destination_longitude'),
            'price' => $validated['price'],
            'trip_date' => $validated['trip_date'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'route' => $route]);
        }

        return redirect()->route('routes.index')->with('success', 'Route updated successfully!');
    }

    // Delete a route
    public function destroy(Request $request, $id)
    {
        $route = Route::findOrFail($id);
        $route->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Route deleted successfully!']);
        }

        return redirect()->back()->with('success', 'Route deleted successfully!');
    }

    // Search routes
    public function search(Request $request)
    {
        try {
            $query = Route::with('bus');

            if ($request->filled('source')) {
                $query->where('source', 'LIKE', '%' . $request->source . '%');
            }
            if ($request->filled('destination')) {
                $query->where('destination', 'LIKE', '%' . $request->destination . '%');
            }
            if ($request->filled('date')) {
               $query->whereDate('trip_date', $request->date);
            }

            $routes = $query->orderBy('trip_date', 'desc')->get();
            $buses = Bus::all();

            return response()->json(['success' => true, 'routes' => $routes, 'buses' => $buses]);
        } catch (\Exception $e) {
            Log::error('Route search error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to search routes'], 500);
        }
    }
}
