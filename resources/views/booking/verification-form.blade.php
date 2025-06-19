@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-header bg-info text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-qrcode me-2"></i>Ticket Verification
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('booking.verify') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="code" class="form-label">
                                <i class="fas fa-key me-2"></i>Enter Verification Code
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg text-center" 
                                   id="code" 
                                   name="code" 
                                   placeholder="e.g., ABC12345"
                                   required
                                   style="letter-spacing: 2px; font-family: monospace;">
                            <div class="form-text">
                                Enter the 8-character verification code from your ticket
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-search me-2"></i>Verify Ticket
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <h5 class="mb-3">Or scan QR Code</h5>
                        <p class="text-muted">
                            <i class="fas fa-mobile-alt me-2"></i>
                            Use your mobile device to scan the QR code on your ticket
                        </p>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-info" onclick="startQRScanner()">
                                <i class="fas fa-camera me-2"></i>Open QR Scanner
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="fas fa-shield-alt me-1"></i>
                        Secure verification system
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-qrcode me-2"></i>QR Code Scanner
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qr-reader" style="width: 100%;"></div>
                <div id="qr-result" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<script>
function startQRScanner() {
    // For demo purposes - in real implementation, you'd use a QR scanner library
    // like html5-qrcode or zxing-js
    alert('QR Scanner would open here. For now, please enter the verification code manually.');
    
    // Example implementation with html5-qrcode (commented out):
    /*
    $('#qrScannerModal').modal('show');
    
    const html5QrCode = new Html5Qrcode("qr-reader");
    html5QrCode.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        (decodedText, decodedResult) => {
            try {
                const qrData = JSON.parse(decodedText);
                if (qrData.verification_code) {
                    document.getElementById('code').value = qrData.verification_code;
                    $('#qrScannerModal').modal('hide');
                    html5QrCode.stop();
                }
            } catch (e) {
                document.getElementById('qr-result').innerHTML = 
                    '<div class="alert alert-warning">Invalid QR code format</div>';
            }
        },
        (errorMessage) => {
            // Handle scan error
        }
    );
    */
}

// Auto-format the verification code input
document.getElementById('code').addEventListener('input', function(e) {
    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (value.length > 8) {
        value = value.substring(0, 8);
    }
    e.target.value = value;
});
</script>
@endsection
