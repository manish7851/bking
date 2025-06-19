<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }    /**
     * Show the user login form.
     */
    public function showUserLoginForm()
    {
        return view('users.userlogin');
    }

    /**
     * Handle user login
     */    public function userLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $attempt = Auth::guard('customer')->attempt($credentials);
        Log::info('Login attempt', [
            'email' => $request->email,
            'attempt_result' => $attempt,
            'session_customer_id' => session('customer_id'),
            'session_all' => session()->all(),
        ]);
        if ($attempt) {
            $request->session()->regenerate();
            
            // Set customer session data for CustomerAuth middleware
            $customer = Auth::guard('customer')->user();
            session([
                'customer_id' => $customer->id,
                'customer_name' => $customer->customer_name
            ]);
            
            // Check if there's a redirect URL
            $redirectUrl = $request->input('redirect_after_login');
            if ($redirectUrl) {
                return redirect($redirectUrl);
            }
            
            return redirect()->intended('/userdashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }/**
     * Show the user registration form.
     */
    public function showUserRegisterForm()
    {
        return view('users.userregister');
    }

    /**
     * Handle user registration
     */    public function userRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
            'customer_contact' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect('userregister')
                        ->withErrors($validator)
                        ->withInput();
        }

        $customer = Customer::create([
            'customer_name' => $request->customer_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'customer_contact' => $request->customer_contact,
            'customer_address' => $request->customer_address,
        ]);

        Auth::guard('customer')->login($customer);

        return redirect('/userdashboard');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    /**
     * Handle user logout
     */
    public function userLogout(Request $request)
    {
        Auth::guard('customer')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
