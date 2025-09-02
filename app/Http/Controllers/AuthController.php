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

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);


    if (Auth::attempt($credentials)) {
        $user = Auth::user();

        // API request? Check Accept header
        if ($request->expectsJson()) {
            // Generate Sanctum token for API
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ], 200);
        }

        // Web redirect
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }

    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}



 
    public function showUserLoginForm()
    {
        return view('users.userlogin');
    }
 
public function userLogin(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $attempt = Auth::guard('customer')->attempt($credentials);

    if ($attempt) {
        $customer = Auth::guard('customer')->user();
        session([
            'customer_id' => $customer->id,
            'customer_name' => $customer->customer_name
        ]);

        // API request? Check Accept header
        if ($request->expectsJson()) {
            $token = $customer->createToken('API Token')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'customer' => $customer,
                'token' => $token
            ], 200);
        }

        // Web redirect
        return redirect()->intended('/userdashboard');
    }

    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ]);
}


    public function showUserRegisterForm()
    {
        return view('users.userregister');
    }


  public function userRegister(Request $request)
{
    $validator = Validator::make($request->all(), [
        'customer_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:customers',
        'password' => 'required|string|min:8|confirmed',
        'customer_contact' => 'required|string|max:20',
        'customer_address' => 'required|string|max:500',
    ]);

    if ($validator->fails()) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        return redirect('userregister')
                    ->withErrors($validator)
                    ->withInput();
    }

    $customer = Customer::create([
        'customer_name' => $request->customer_name,
        'email' => $request->email,
        'password' => $request->password,
        'customer_contact' => $request->customer_contact,
        'customer_address' => $request->customer_address,
    ]);

    Auth::guard('customer')->login($customer);

    if ($request->expectsJson()) {
        $token = $customer->createToken('mobile')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'customer' => $customer,
            'token' => $token
        ], 201);
    }

    return redirect('/userdashboard');
}


public function logout(Request $request)
{
    Auth::logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($request->expectsJson()) {
        return response()->json([           
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    return redirect('/');
}

public function userLogout(Request $request)
{
    Auth::guard('customer')->logout();
    
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    return redirect('/');
}

    
}
