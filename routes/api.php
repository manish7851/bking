<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\BookingController;

// Public routes
Route::post('/signup', [AdminController::class, 'register']); 
Route::post('/register', [AdminController::class, 'register']); 
Route::post('/login', [AdminController::class, 'login']);
Route::get('/admins', [AdminController::class, 'index']); 

Route::get('/routes', [RouteController::class, 'index'])->name('routes.index');
Route::get('/routes/search', [RouteController::class, 'search'])->name('search');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/routes', [RouteController::class, 'index']);
    Route::get('routes/{route}/buses', [BusController::class, 'getBuses']);
    Route::get('bookings/seats', [BookingController::class, 'getBookedSeats']);
});
