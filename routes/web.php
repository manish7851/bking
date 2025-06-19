<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\BusController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

    // Root route - always show home page
Route::get('/', function () {
    return view('home');
});
// Removed duplicate userdashboard route - now handled in routes_web.php with proper customer middleware

Route::get('/profile', function () {
    return view('profile.show');
})->name('profile.show');

Route::get('/userlogin', [App\Http\Controllers\AuthController::class, 'showUserLoginForm'])->name('userlogin');
Route::post('/userlogin', [App\Http\Controllers\AuthController::class, 'userLogin'])->name('userlogin.attempt');

Route::get('/home', function () {
    return view('home');
});

Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

// Route::get('/userdashboard', [App\Http\Controllers\UserController::class, 'dashboard'])->name('userdashboard');
Route::get('/bookings/create', [App\Http\Controllers\BookingController::class, 'create'])->name('bookings.create');
Route::post('/bookings/store', [App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
Route::get('/check-seat-availability', [BookingController::class, 'checkSeatAvailability'])->name('check.seat.availability');
Route::post('/check-seat-availability', [BookingController::class, 'checkSeatAvailability'])->name('check.seat.availability.post');
Route::get('/booking/download/{id}', [App\Http\Controllers\TicketController::class, 'download'])->name('booking.download');
Route::delete('/bookings/{id}', [BookingController::class, 'destroy'])->name('bookings.destroy');
Route::put('/bookings/{id}', [App\Http\Controllers\BookingController::class, 'update'])->name('bookings.update');
Route::get('/buses', [App\Http\Controllers\BusController::class, 'index'])->name('buses.index');
Route::post('/buses', [App\Http\Controllers\BusController::class, 'store'])->name('buses.store');
Route::get('/buses/{id}/edit', [App\Http\Controllers\BusController::class, 'edit'])->name('buses.edit');
Route::get('/buses/{id}/track', [App\Http\Controllers\BusController::class, 'trackBus'])->name('buses.track');
Route::patch('/buses/{id}/tracking', [App\Http\Controllers\BusController::class, 'toggleTracking'])->name('buses.tracking');
Route::delete('/buses/{id}', [App\Http\Controllers\BusController::class, 'destroy'])->name('buses.destroy');
Route::get('/map/buses', [BusController::class, 'map'])->name('buses.map');
Route::get('/routes', [App\Http\Controllers\RouteController::class, 'index'])->name('routes.index');
Route::get('/routes/{id}/edit', [App\Http\Controllers\RouteController::class, 'edit'])->name('routes.edit');
Route::post('/routes/store', [App\Http\Controllers\RouteController::class, 'store'])->name('routes.store');
Route::delete('/routes/{id}', [App\Http\Controllers\RouteController::class, 'destroy'])->name('routes.destroy');

Route::get('/userbookings/search', [App\Http\Controllers\BookingController::class, 'fetchRoutes'])->name('userbookings.search');
Route::get('/geofences', [App\Http\Controllers\GeofenceController::class, 'index'])->name('geofences.index');
Route::delete('/geofences/{id}', [App\Http\Controllers\GeofenceController::class, 'destroy'])->name('geofences.destroy');

 
Route::get('/payment/esewa/{bookingId}', [App\Http\Controllers\PaymentController::class, 'initiateEsewaPayment'])->name('payment.esewa.payment');
Route::post('/payment/khalti/verify', [App\Http\Controllers\PaymentController::class, 'verifyKhaltiPayment'])->name('payment.khalti.verify');

// Ticket history and booking management
Route::get('/user/tickets', [App\Http\Controllers\BookingController::class, 'showUserTickets'])->name('user.tickets');
Route::get('/booking/confirmation/{id}', [App\Http\Controllers\BookingController::class, 'confirmation'])->name('booking.confirmation');
Route::get('/booking/success/{id}', [App\Http\Controllers\BookingController::class, 'bookingSuccess'])->name('booking.success');
Route::group(['middleware' => ['auth']], function () {
    Route::get('/bookings', [App\Http\Controllers\BookingController::class, 'index'])->name('bookings_page');
});

// Dashboard routes
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/tracking', [App\Http\Controllers\DashboardController::class, 'tracking'])->name('dashboard.tracking');

// Bus tracking data routes
Route::get('/buses/track/all', [App\Http\Controllers\BusController::class, 'trackAllBuses'])->name('buses.track.all');
Route::get('/buses/track/all/data', [App\Http\Controllers\BusController::class, 'getLiveBuses'])->name('buses.track.all.data');
Route::get('/buses/{id}/locations', [App\Http\Controllers\BusController::class, 'getLocations'])->name('buses.locations');

Route::match(['get', 'post'], '/logout', function(Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');
Route::match(['get', 'post'], '/login', function (\Illuminate\Http\Request $request) {
    if ($request->isMethod('post')) {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }
        return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
    }
    return view('auth.login');
})->name('login');
// User booking routes moved to routes_web.php with proper customer middleware

// QR Verification routes
Route::get('/verify', [App\Http\Controllers\QRVerificationController::class, 'showForm'])->name('booking.verify.form');
Route::post('/verify', [App\Http\Controllers\QRVerificationController::class, 'verify'])->name('booking.verify');
Route::get('/verify/{code}', [App\Http\Controllers\QRVerificationController::class, 'verifyCode'])->name('booking.verify.code');

// Payment routes
Route::prefix('payment')->group(function () {
    // eSewa routes
    Route::get('success-messeage', [App\Http\Controllers\EsewaPaymentController::class, 'success'])->name('payment.success-message');
    Route::get('failure', [App\Http\Controllers\EsewaPaymentController::class, 'failure'])->name('payment.esewa.failure');
});

// eSewa checkout route
Route::post('/esewa/checkout', [App\Http\Controllers\EsewaPaymentController::class, 'checkout'])->name('esewa.checkout');
Route::get('/userdashboard', [App\Http\Controllers\DashboardController::class, 'userDashboard'])
    ->middleware('customer.auth')
    ->name('userdashboard');
Route::get('/userbookings', [App\Http\Controllers\BookingController::class, 'usersBookings'])->name('userbookings');

// Customer password reset routes
Route::get('customer/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('customer.password.request');
Route::post('customer/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('customer.password.email');
Route::get('customer/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('customer.password.reset');
Route::post('customer/password/reset', [ResetPasswordController::class, 'reset'])->name('customer.password.update');

// Static pages
Route::view('/about', 'about')->name('about');
Route::view('/home', 'home')->name('home');
Route::view('/contact', 'contact')->name('contact');

// User registration routes
Route::get('/userregister', [App\Http\Controllers\AuthController::class, 'showUserRegisterForm'])->name('userregister');
Route::post('/userregister', [App\Http\Controllers\AuthController::class, 'userRegister'])->name('userregister.attempt');
Route::get('/bookings/{id}/edit', [App\Http\Controllers\BookingController::class, 'edit'])->name('bookings.edit');
// RESTful booking creation route for AJAX
Route::post('/bookings', [App\Http\Controllers\BookingController::class, 'store']);
Route::put('/buses/{id}', [App\Http\Controllers\BusController::class, 'update'])->name('buses.update');

// Route management
Route::prefix('routes')->group(function() {
    Route::get('/', [App\Http\Controllers\RouteController::class, 'index'])->name('routes.index');
    Route::get('/create', [App\Http\Controllers\RouteController::class, 'create'])->name('routes.create');
    Route::post('/', [App\Http\Controllers\RouteController::class, 'store'])->name('routes.store');
    Route::get('/{route}/edit', [App\Http\Controllers\RouteController::class, 'edit'])->name('routes.edit');
    Route::put('/{route}', [App\Http\Controllers\RouteController::class, 'update'])->name('routes.update');
    Route::delete('/{route}', [App\Http\Controllers\RouteController::class, 'destroy'])->name('routes.destroy');
    Route::get('/track/{route}', [App\Http\Controllers\RouteController::class, 'trackRoute'])->name('routes.track');
});
Route::get('/buses/{id}/route-coordinates', [App\Http\Controllers\BusController::class, 'getRouteCoordinates'])->name('buses.route-coordinates');
Route::get('/routes/picker', [App\Http\Controllers\RouteController::class, 'showRoutePicker'])->name('routes.picker');
// Custom path API for buses
Route::post('/buses/{id}/custom-path', [App\Http\Controllers\BusController::class, 'saveCustomPath'])->name('buses.custom-path.save');
Route::get('/buses/{id}/custom-path', [App\Http\Controllers\BusController::class, 'getCustomPath'])->name('buses.custom-path.get');

