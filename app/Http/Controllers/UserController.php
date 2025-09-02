<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Handle login (Web + JSON)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => Auth::user()
                ]);
            }

            return redirect()->route('user.dashboard');
        }

        $errorMessage = 'The provided credentials do not match our records.';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }

        return back()->withErrors([
            'email' => $errorMessage,
        ])->onlyInput('email');
    }

    /**
     * Show user dashboard (Web + JSON)
     */
    public function dashboard(Request $request)
    {
        $bookings = \App\Models\Booking::where('user_id', Auth::id())->get();
        $allBookings = \App\Models\Booking::all();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'user' => Auth::user(),
                'bookings' => $bookings,
                'allBookings' => $allBookings,
            ]);
        }

        return view('userdashboard', compact('bookings', 'allBookings'));
    }
}
