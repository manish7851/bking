<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class ProfileController extends Controller
{
    public function show()
    {
        $customer = Customer::findOrFail(session('customer_id'));
        return view('profile.show', compact('customer'));
    }

    public function edit()
    {
        $customer = Customer::findOrFail(session('customer_id'));
        return view('profile.edit', compact('customer'));
    }

    public function update(Request $request)
    {
        $customer = Customer::findOrFail(session('customer_id'));
        
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'required|string|max:255',
            'customer_contact' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:customers,email,'.$customer->id,
        ]);

        $customer->update($validated);

        return redirect()->route('profile.show')->with('success', 'Profile updated successfully');
    }
}
