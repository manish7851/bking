<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Display all customers
   public function index()
{
    $customers = Customer::all();
    return view('customer.customers', compact('customers'));
}


    // Show the form to create a new customer
    public function create()
    {
        return view('customers.create');
    }

    // Store a newly created customer in the database
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|max:255',
            'email' => 'required|email|unique:customers,email,' . $id,
            'password' => 'nullable|min:6|', // Make sure password is confirmed
            'customer_address' => 'required|string',
            'customer_contact' => 'required|string',
        ]);
    
        $customer = Customer::find($id);
        $customer->customer_name = $request->customer_name;
        $customer->customer_contact = $request->customer_contact;
        $customer->email = $request->email;
        $customer->customer_address = $request->customer_address;
    
        if ($request->filled('password')) {
            $customer->password = bcrypt($request->password);
        }
    
        $customer->save();
    
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully');
    }
    
    public function destroy($id)
    {
        $customer = Customer::find($id);
        $customer->delete();
    
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'customer_name' => 'required|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6|', // Password should be confirmed
            'customer_address' => 'required|string',
            'customer_contact' => 'required|string',
        ]);
    
        // Create the new customer
        $customer = new Customer();
        $customer->customer_name = $request->customer_name;
        $customer->email = $request->email;
        $customer->customer_address = $request->customer_address;
        $customer->customer_contact = $request->customer_contact;
        $customer->password = bcrypt($request->password); // Encrypt the password
        $customer->created_at = now(); // Manually set created_at
        $customer->updated_at = now(); // Manually set updated_at
        // Save the customer to the database
        $customer->save();
    
        // Redirect with success message
        return redirect()->route('customers.index')->with('success', 'Customer created successfully');
    }
    


    
}
