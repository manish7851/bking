@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="alert alert-success mt-4">
                <h4 class="alert-heading">Password Reset Successful!</h4>
                <p>Your password has been reset. You can now <a href="{{ route('userlogin') }}">login</a> with your new password.</p>
            </div>
        </div>
    </div>
</div>
@endsection
