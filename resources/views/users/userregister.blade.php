<div class="container d-flex align-items-center justify-content-center min-vh-100" style="background: linear-gradient(135deg, #f8fafc 0%, #e0e7ef 100%);">
    <div class="row w-100 justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-success text-white text-center rounded-top-4">
                    <h3 class="mb-0">User Registration</h3>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('userregister.attempt') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control form-control-lg" id="customer_name" name="customer_name" required placeholder="Enter your name">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" required placeholder="Enter your email">
                        </div>                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control form-control-lg" id="password" name="password" required placeholder="Create a password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control form-control-lg" id="password_confirmation" name="password_confirmation" required placeholder="Confirm your password">
                        </div>
                        <div class="mb-3">
                            <label for="customer_contact" class="form-label">Contact Number</label>
                            <input type="text" class="form-control form-control-lg" id="customer_contact" name="customer_contact" required placeholder="Enter your contact number">
                        </div>
                        <div class="mb-3">
                            <label for="customer_address" class="form-label">Address</label>
                            <input type="text" class="form-control form-control-lg" id="customer_address" name="customer_address" required placeholder="Enter your address">
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100">Register</button>
                    </form>
                </div>
                <div class="card-footer text-center bg-white rounded-bottom-4">
                    <span class="text-muted">Already have an account?</span>
                    <a href="/userlogin" class="ms-2">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

