@extends('layouts.app')

 
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-danger text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Verification Failed
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle fs-1 mb-3"></i>
                        <h4>Invalid Ticket</h4>
                        <p class="mb-0">{{ $message }}</p>
                    </div>

                    <div class="mt-4">
                        <h5>Possible Reasons:</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="fas fa-dot-circle text-danger me-2"></i>
                                Incorrect verification code
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-dot-circle text-danger me-2"></i>
                                Ticket has been cancelled
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-dot-circle text-danger me-2"></i>
                                Payment not completed
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-dot-circle text-danger me-2"></i>
                                Invalid or damaged QR code
                            </li>
                        </ul>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> If you believe this is an error, please contact customer support with your booking details.
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('booking.verify.form') }}" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>Try Again
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.fs-1 {
    font-size: 3rem !important;
}
</style>
 
