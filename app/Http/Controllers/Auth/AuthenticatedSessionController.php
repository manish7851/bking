<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    // Logout method
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();


        return redirect('/login'); // or any page you want to redirect to
    }



    public function store(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($request->only('email', 'password'))) {
        session()->flash('success', 'Successfully logged in!');
        return redirect()->intended('dashboard'); // redirect to the dashboard
    }

    return back()->withErrors(['email' => 'Invalid credentials.']);
}

}
