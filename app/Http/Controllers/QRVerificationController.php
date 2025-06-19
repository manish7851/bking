<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Services\QRCodeService;

class QRVerificationController extends Controller
{
    protected $qrService;

    public function __construct(QRCodeService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Show QR verification form
     */
    public function showForm()
    {
        return view('qr.verify_form');
    }

    /**
     * Verify QR code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string'
        ]);

        $qrCode = $request->input('qr_code');
        
        // Find booking by QR code
        $booking = Booking::where('qr_code', $qrCode)->first();

        if (!$booking) {
            return back()->with('error', 'Invalid QR code. Booking not found.');
        }

        return view('qr.verification_result', compact('booking'));
    }

    /**
     * Verify QR code directly from URL
     */
    public function verifyCode($code)
    {
        $booking = Booking::where('qr_code', $code)->first();

        if (!$booking) {
            return view('qr.verification_result', [
                'booking' => null,
                'error' => 'Invalid QR code. Booking not found.'
            ]);
        }

        return view('qr.verification_result', compact('booking'));
    }
}
