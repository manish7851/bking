@extends('layouts.app')

 
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-danger text-center">
                <h3 class="mb-3">Payment Error</h3>
                <p>{{ $message ?? 'An error occurred during payment processing.' }}</p>
                <a href="/userdashboard" class="btn btn-primary mt-3">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
 
