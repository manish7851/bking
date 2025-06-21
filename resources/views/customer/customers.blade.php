@extends('layouts.dashboard')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 style="margin-left: 200px;">All Customers</h2>
        <!-- Add Customer Modal Trigger -->
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">+ Add Customer</button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

   <table class="table table-bordered table-striped" style="margin-top:50px; width:2px; margin-left:150px; width:max-content">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Customer Name</th>
            <th>Address</th>
            <th>Contact Number</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>    <tbody>
 @forelse($customers as $index => $customer)
    @continue(!$customer || !isset($customer->customer_name, $customer->customer_address, $customer->email, $customer->password, $customer->customer_contact))
    <tr>
        <td>{{ $index + 1 }}</td>
        <td>{{ $customer->customer_name }}</td>
        <td>{{ $customer->customer_address }}</td>
        <td>{{ $customer->customer_contact }}</td>
        <td>{{ $customer->email }}</td>
        <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCustomerModal" 
                data-id="{{ $customer->id }}" data-name="{{ $customer->customer_name }}" data-address="{{ $customer->customer_address }}"
                data-email="{{ $customer->email }}" data-password="{{ $customer->password }}" data-contact="{{ $customer->customer_contact }}">
                Edit
            </button>
            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger">Delete</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6">No customers found.</td>
    </tr>
@endforelse


    </tbody>
</table>

</div>

<!-- Add Customer Modal -->
<div class="modal fade @if($errors->any()) show d-block @endif" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true" style= "@if($errors->any()) background: rgba(0,0,0,0.5); @endif">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" required value="{{ old('customer_name') }}">
                    </div>
                    <div class="mb-3">
                        <label for="customer_address">Customer Address</label>
                        <input type="text" name="customer_address" class="form-control" required value="{{ old('customer_address') }}">
                    </div>
                    <div class="mb-3">
                        <label for="email">Email</label>
                        <input type="email" name="email" class="form-control" required value="{{ old('email') }}">
                    </div>
                    <div class="mb-3">
                        <label for="customer_contact">Contact Number</label>
                        <input type="text" name="customer_contact" class="form-control" required value="{{ old('customer_contact') }}">
                    </div>
                    <div class="mb-3">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required>
                        <button type="button" id="toggle-password">Show</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCustomerForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_customer_name">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" id="edit_customer_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_customer_address">Customer Address</label>
                        <input type="text" name="customer_address" class="form-control" id="edit_customer_address" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email">Email</label>
                        <input type="email" name="email" class="form-control" id="edit_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_customer_contact">Contact Number</label>
                        <input type="text" name="customer_contact" class="form-control" id="edit_customer_contact" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password">Password</label>
                        <input type="password" name="password" class="form-control" id="edit_password">
                    </div>
         

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Auto-show modal on validation error -->
@if($errors->any())
<script>
    var myModal = new bootstrap.Modal(document.getElementById('addCustomerModal'), {
        backdrop: 'static'
    });
    myModal.show();
</script>
@endif

<!-- Edit Customer Modal Script -->
<script>
    var editCustomerModal = document.getElementById('editCustomerModal');
    if (editCustomerModal) {
        editCustomerModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            if (!button) return;
            var customerId = button.getAttribute('data-id');
            var customerName = button.getAttribute('data-name');
            var customerAddress = button.getAttribute('data-address');
            var customerEmail = button.getAttribute('data-email');
            var customerPassword = button.getAttribute('data-password');
            var customerContact = button.getAttribute('data-contact');

            var form = document.getElementById('editCustomerForm');
            form.action = '/customers/' + customerId;

            var nameField = document.getElementById('edit_customer_name');
            var addressField = document.getElementById('edit_customer_address');
            var emailField = document.getElementById('edit_email');
            var customerContactField = document.getElementById('edit_customer_contact');
            var passwordField = document.getElementById('edit_password');

            nameField.value = customerName || '';
            addressField.value = customerAddress || '';
            emailField.value = customerEmail || '';
            customerContactField.value = customerContact || '';
            passwordField.value = customerPassword || '';
        });
    }
</script>


<script>
    document.getElementById('toggle-password').addEventListener('click', function() {
        var passwordField = document.getElementById('password');
        var toggleButton = document.getElementById('toggle-password');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleButton.textContent = 'Hide';
        } else {
            passwordField.type = 'password';
            toggleButton.textContent = 'Show';
        }
    });
</script>

