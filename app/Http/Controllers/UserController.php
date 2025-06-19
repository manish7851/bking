<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
       public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
if (Auth::attempt($credentials, $request->filled('remember'))) {
    $request->session()->regenerate();
    return redirect()->route('user.dashboard');
}

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $bookings = \App\Models\Booking::where('user_id', Auth::id())
            ->get();
        $allBookings = \App\Models\Booking::all(); // Fetch all bookings
        return view('userdashboard', compact('bookings', 'allBookings'));
    }
}
