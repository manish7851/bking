<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BusTrackingApiController;
use App\Http\Controllers\BusController;

Route::get('/test-route', function () {
    return response()->json(['message' => 'Test OK']);
});
// // GPS tracking endpoint for bus location updates
Route::post('/bus/location/update', [BusTrackingApiController::class, 'updateLocation']);

// // GPS tracking endpoint for bus location updates from TCP server
// Route::post('/bus/location/update-gps', [BusController::class, 'updateGps']);

// // Route to get bus information by IMEI number
// Route::get('/bus/by-imei', [BusTrackingApiController::class, 'getBusByImei']);

// // Bus location API endpoints
// Route::prefix('buses')->group(function () {
//     // Get all live/active buses
//     Route::get('/live', [BusController::class, 'getLiveBuses']);
    
//     // Get single bus location
//     Route::get('/{id}/location', [BusController::class, 'getLocation']);
    
//     // Get single bus location history
//     Route::get('/{id}/location-history', [BusController::class, 'locationHistory']);
    
//     // Get single bus locations with bus info
//     Route::get('/{id}/locations', [BusController::class, 'getLocations']);
// });

// // Update bus location manually (for testing or manual updates)
// Route::post('/bus/{id}/update-location', [BusController::class, 'updateLocation']);

// // (Optional) Add more API routes as needed
// Route::post('/bus/start-tracking', [BusTrackingApiController::class, 'startTracking']);
// Route::post('/bus/end-tracking', [BusTrackingApiController::class, 'endTracking']);
Route::get('/bus/location/history/{id}', [BusTrackingApiController::class, 'getLocationHistory']);
