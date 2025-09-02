<?php

use App\Http\Controllers\Api\BusTrackingApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\BusController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AuthController;

Route::post('/admins', [AuthController::class, 'userCreate']);
// Route::post('/login', [AuthController::class, 'login']);
// Route::get('/admins', [AdminController::class, 'index']);
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
    $customer = null;
    if (session('customer_id')) {
        $customer = \App\Models\Customer::find(session('customer_id'));
    }
    return view('profile.show', compact('customer'));
})->name('profile.show');

Route::get('/profile/edit', function () {
    $customer = null;
    if (session('customer_id')) {
        $customer = \App\Models\Customer::find(session('customer_id'));
    }
    return view('profile.edit', compact('customer'));
})->name('profile.edit');

Route::get('/userlogin', [App\Http\Controllers\AuthController::class, 'showUserLoginForm'])->name('userlogin');
Route::post('/userlogin', [App\Http\Controllers\AuthController::class, 'userLogin'])->name('userlogin.attempt');

Route::get('/home', function () {
    return view('home');
});

Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');

// Route::get('/userdashboard', [App\Http\Controllers\UserController::class, 'dashboard'])->name('userdashboard');
// Admin booking routes
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/bookings/create', [App\Http\Controllers\BookingController::class, 'create'])->name('admin.bookings.create');
});

// User booking routes
Route::middleware(['customer.auth'])->group(function () {
    Route::match(['get', 'post'], '/bookings/create', [App\Http\Controllers\BookingController::class, 'create'])->name('bookings.create');
});
Route::post('/booking/prepare', [App\Http\Controllers\BookingController::class, 'prepareBooking'])->name('booking.prepare');
Route::post('/bookings/store', [App\Http\Controllers\BookingController::class, 'store'])->name('bookings.store');
Route::get('/check-seat-availability', [BookingController::class, 'checkSeatAvailability'])->name('check.seat.availability');
Route::get('/bookings/booked_seats/{route_id}', [BookingController::class, 'getBookedSeats'])->name('bookings.booked_seats');
Route::post('/check-seat-availability', [BookingController::class, 'checkSeatAvailability'])->name('check.seat.availability.post');
Route::get('/booking/download/{id}', [App\Http\Controllers\TicketController::class, 'download'])->name('booking.download');
Route::get('/track/{bus_id}/route/{active_route_id}', [App\Http\Controllers\TicketController::class, 'trackBusRoute'])->name('booking.trackBusActiveRoute');
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
Route::get('/routes/search', [App\Http\Controllers\RouteController::class, 'search'])->name('routes.search');
Route::get('/routes/{id}/edit', [App\Http\Controllers\RouteController::class, 'edit'])->name('routes.edit');
Route::post('/routes/store', [App\Http\Controllers\RouteController::class, 'store'])->name('routes.store');
Route::delete('/routes/{id}', [App\Http\Controllers\RouteController::class, 'destroy'])->name('routes.destroy');

// User booking routes
Route::middleware(['customer.auth'])->group(function () {
    Route::get('/userbookings/search', [App\Http\Controllers\BookingController::class, 'fetchRoutes'])->name('userbookings.search');
    Route::post('/userbookings/pickupdropff', [App\Http\Controllers\BookingController::class, 'savePickupDropoff'])->name('userbookings.picupdropoff');
    Route::get('/userbookings/create', [App\Http\Controllers\BookingController::class, 'userCreate'])->name('userbookings.create');
    Route::post('/userbookings/create', [App\Http\Controllers\BookingController::class, 'userStore'])->name('userbookings.store');
});
Route::get('/geofences', [App\Http\Controllers\GeofenceController::class, 'index'])->name('geofences.index');
Route::delete('/geofences/{id}', [App\Http\Controllers\GeofenceController::class, 'destroy'])->name('geofences.destroy');


// eSewa Payment Routes
Route::get('/payment/esewa/{bookingId}', [App\Http\Controllers\PaymentController::class, 'initiateEsewaPayment'])->name('payment.esewa.payment');
Route::get('/payment/esewa/success', [App\Http\Controllers\PaymentController::class, 'esewaSuccess'])->name('payment.esewa.success');
Route::get('/payment/esewa/failure', [App\Http\Controllers\PaymentController::class, 'esewaFailure'])->name('payment.esewa.failure');
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

Route::match(['get', 'post'], '/logout', function (Request $request) {
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

// Payment and User Booking routes
Route::middleware(['customer.auth'])->group(function () {
    // Booking routes
    Route::get('/userbookings/create', [App\Http\Controllers\BookingController::class, 'userCreate'])->name('userbookings.create');
    Route::post('/userbookings/store', [App\Http\Controllers\BookingController::class, 'store'])->name('userbookings.store');

    // eSewa routes
    Route::post('payment/esewa/process', [App\Http\Controllers\EsewaPaymentController::class, 'process'])->name('payment.esewa.process');
    Route::get('/payment/esewa/success', [PaymentController::class, 'esewaSuccess'])->name('payment.esewa.success');
    Route::get('payment/esewa/failure', [App\Http\Controllers\EsewaPaymentController::class, 'failure'])->name('payment.esewa.failure');

    // Khalti routes
    Route::prefix('payment/khalti')->group(function () {
        Route::post('/verify', [App\Http\Controllers\PaymentController::class, 'verifyKhaltiPayment'])->name('payment.khalti.verify');
    });
});

Route::get('/userdashboard', [App\Http\Controllers\DashboardController::class, 'userDashboard'])
    ->middleware('customer.auth')
    ->name('userdashboard');
Route::get('/userbookings', [App\Http\Controllers\BookingController::class, 'usersBookings'])->name('userbookings');
Route::get('/userbookings/search', [App\Http\Controllers\UserBookingController::class, 'search'])->name('userbookings.search'); // Customer password reset routes
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
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showUserRegisterForm'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'userRegister'])->name('register.attempt');
Route::get('/bookings/{id}/edit', [App\Http\Controllers\BookingController::class, 'edit'])->name('bookings.edit');
// RESTful booking creation route for AJAX
Route::post('/bookings', [App\Http\Controllers\BookingController::class, 'store']);
Route::put('/buses/{id}', [App\Http\Controllers\BusController::class, 'update'])->name('buses.update');

// Route management
Route::prefix('routes')->group(function () {
    Route::get('/', [App\Http\Controllers\RouteController::class, 'index'])->name('routes.index');
    Route::get('/create', [App\Http\Controllers\RouteController::class, 'create'])->name('routes.create');
    Route::post('/', [App\Http\Controllers\RouteController::class, 'store'])->name('routes.store');
    Route::get('/{route}/edit', [App\Http\Controllers\RouteController::class, 'edit'])->name('routes.edit');
    Route::get('/{route}', [App\Http\Controllers\RouteController::class, 'update'])->name('routes.update');
    Route::delete('/{route}', [App\Http\Controllers\RouteController::class, 'destroy'])->name('routes.destroy');
    Route::get('/track/{route}', [App\Http\Controllers\RouteController::class, 'trackRoute'])->name('routes.track');
});
Route::get('/buses/{id}/route-coordinates', [App\Http\Controllers\BusController::class, 'getRouteCoordinates'])->name('buses.route-coordinates');
Route::get('/routes/picker', [App\Http\Controllers\RouteController::class, 'showRoutePicker'])->name('routes.picker');
// Custom path API for buses
Route::post('/buses/{id}/custom-path', [App\Http\Controllers\BusController::class, 'saveCustomPath'])->name('buses.custom-path.save');
Route::get('/buses/{id}/custom-path', [App\Http\Controllers\BusController::class, 'getCustomPath'])->name('buses.custom-path.get');
// Booking preparation route (no auth required)
Route::post('booking/prepare', [App\Http\Controllers\BookingController::class, 'prepareBooking'])->name('booking.prepare');

// User payment routes
Route::get('/payment/user/esewa/success', [App\Http\Controllers\PaymentController::class, 'userEsewaSuccess'])->name('payment.user.esewa.success');
Route::get('/payment/user/esewa/failure', [App\Http\Controllers\PaymentController::class, 'userEsewaFailure'])->name('payment.user.esewa.failure');

// Admin payment routes
Route::get('/payment/admin/esewa/success', [App\Http\Controllers\PaymentController::class, 'adminEsewaSuccess'])->name('payment.admin.esewa.success');
Route::get('/payment/admin/esewa/failure', [App\Http\Controllers\PaymentController::class, 'adminEsewaFailure'])->name('payment.admin.esewa.failure');
Route::get('/usersbookings', function () {
    $bookings = collect();
    if (session('customer_id')) {
        $bookings = \App\Models\Booking::where('customer_id', session('customer_id'))
            ->orderBy('created_at', 'desc')
            ->with(['route.bus'])
            ->get();
    }
    return view('users.userbookings', compact('bookings'));
})->name('usersbookings');
Route::match(['post', 'put'], '/profile/update', function (\Illuminate\Http\Request $request) {
    if (!session('customer_id')) {
        return redirect()->route('userlogin')->with('error', 'Please login to update your profile.');
    }
    $customer = \App\Models\Customer::find(session('customer_id'));
    if (!$customer) {
        return redirect()->route('profile.show')->with('error', 'Customer not found.');
    }
    $customer->customer_name = $request->input('customer_name');
    $customer->email = $request->input('email');
    $customer->customer_contact = $request->input('customer_contact');
    $customer->customer_address = $request->input('customer_address');
    $customer->save();
    return redirect()->route('profile.show')->with('success', 'Profile updated successfully!');
})->name('profile.update');
Route::get('/buses/{bus}/trackings', function ($bus) {
    $bus = \App\Models\Bus::findOrFail($bus);
    $trackings = $bus->trackings()->orderByDesc('started_at')->get();
    return view('buses.tracking-list', compact('bus', 'trackings'));
})->name('buses.tracking.list');
Route::get('/buses/{bus}/trackings/{tracking}', function ($bus, $tracking) {
    $bus = \App\Models\Bus::findOrFail($bus);
    $tracking = $bus->trackings()->findOrFail($tracking);
    $locations = $tracking->locations()->orderBy('recorded_at')->get();
    return view('buses.tracking-view', compact('bus', 'tracking', 'locations'));
})->name('buses.tracking.show');
Route::delete('/buses/{bus}/trackings/{tracking}', function ($bus, $tracking) {
    $bus = \App\Models\Bus::findOrFail($bus);
    $tracking = $bus->trackings()->findOrFail($tracking);
    $tracking->locations()->delete();
    $tracking->delete();
    return redirect()->route('buses.tracking.list', $bus->id)->with('success', 'Tracking deleted successfully.');
})->name('buses.tracking.delete');

// Bus tracking API web routes
Route::post('/bus/start-tracking', [App\Http\Controllers\Api\BusTrackingApiController::class, 'startTracking'])->name('bus.start-tracking');
Route::post('/bus/end-tracking', [App\Http\Controllers\Api\BusTrackingApiController::class, 'endTracking'])->name('bus.end-tracking');

Route::resource('subscriptions', SubscriptionController::class);
