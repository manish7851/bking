<?php

namespace App\Http\Controllers;
use App\Models\Bus;

use App\Models\Route;
use Illuminate\Http\Request;

class RouteController extends Controller
{
    // Display a listing of the routes
    public function index()
    {
        $routes = Route::with('bus')->get(); // This loads bus relation
        $buses = Bus::all();
        return view('routes.routes', compact('routes', 'buses'));
    }

    // Show the edit form for a specific route
    public function edit($id)
    {
        $route = Route::findOrFail($id);
        $buses = Bus::all();
        return view('routes.edit', compact('route', 'buses'));
    }

    public function store(Request $request)
    {
        // Validate the form inputs
        $validatedData = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'source' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric',
            'trip_date' => 'required|date|after_or_equal:today',
        ]);
    
        // Verify bus exists and is available
        $bus = Bus::findOrFail($validatedData['bus_id']);
        
        // Create a new route
        Route::create([
            'bus_id' => $validatedData['bus_id'],
            'source' => $validatedData['source'],
            'destination' => $validatedData['destination'],
            'source_latitude' => $request->input('source_latitude'),
            'source_longitude' => $request->input('source_longitude'),
            'destination_latitude' => $request->input('destination_latitude'),
            'destination_longitude' => $request->input('destination_longitude'),
            'price' => $validatedData['price'],
            'trip_date' => $validatedData['trip_date'],
        ]);
    
        return redirect()->back()->with('success', 'Route created successfully!');
    }


    public function destroy($id)
{
    $route = Route::findOrFail($id);
    $route->delete();

    return redirect()->back()->with('success', 'Route deleted successfully!');
}


    // Update the route information
    public function update(Request $request, $id)
    {
        // Validate the data
        $validatedData = $request->validate([
            'source' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]);

        // Find the route by ID and update the details
        $route = Route::findOrFail($id);
        $route->source = $request->input('source');
        $route->destination = $request->input('destination');
        $route->source_latitude = $request->input('source_latitude');
        $route->source_longitude = $request->input('source_longitude');
        $route->destination_latitude = $request->input('destination_latitude');
        $route->destination_longitude = $request->input('destination_longitude');
        $route->price = $request->input('price');
        $route->save();

        // Redirect back with a success message
        return redirect()->route('routes.index')->with('success', 'Route updated successfully!');
    }

    public function showRoutePicker()
    {
        return view('routes.route-picker');
    }

   
}
