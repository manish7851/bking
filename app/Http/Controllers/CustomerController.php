<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Display all customers
    public function index(Request $request)
    {
        $customers = Customer::all();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customers' => $customers]);
        }

        return view('customer.customers', compact('customers'));
    }

    // Show the form to create a new customer
    public function create(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Provide customer data to create']);
        }

        return view('customers.create');
    }

    // Store a newly created customer
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6',
            'customer_address' => 'required|string',
            'customer_contact' => 'required|string',
        ]);

        $customer = Customer::create([
            'customer_name' => $request->customer_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'customer_address' => $request->customer_address,
            'customer_contact' => $request->customer_contact,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customer' => $customer, 'message' => 'Customer created successfully']);
        }

        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }

    // Update existing customer
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'password' => 'nullable|min:6',
            'customer_address' => 'required|string',
            'customer_contact' => 'required|string',
        ]);

        $customer = Customer::findOrFail($id);
        $customer->customer_name = $request->customer_name;
        $customer->email = $request->email;
        $customer->customer_address = $request->customer_address;
        $customer->customer_contact = $request->customer_contact;

        if ($request->filled('password')) {
            $customer->password = bcrypt($request->password);
        }

        $customer->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'customer' => $customer, 'message' => 'Customer updated successfully']);
        }

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }

    // Delete customer
    public function destroy(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Customer deleted successfully']);
        }

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }
}
