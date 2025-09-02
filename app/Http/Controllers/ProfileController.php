<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class ProfileController extends Controller
{
    // Show profile (Web + JSON)
    public function show(Request $request)
    {
        $customerId = session('customer_id');

        if (!$customerId) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'User not logged in'], 401);
            }
            return redirect()->route('userlogin')->with('error', 'Please login to view your profile');
        }

        $customer = Customer::findOrFail($customerId);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }

        return view('profile.show', compact('customer'));
    }

    // Edit profile (Web only)
    public function edit()
    {
        $customer = Customer::findOrFail(session('customer_id'));
        return view('profile.edit', compact('customer'));
    }

    // Update profile (Web + JSON)
    public function update(Request $request)
    {
        $customer = Customer::findOrFail(session('customer_id'));

        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
        ]);

        $customer->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'customer' => $customer
            ]);
        }

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully');
    }
}
