<?php

namespace App\Services;

use App\Models\Booking;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Log;

class QRCodeService
{
    /**
     * Generate QR code for a booking
     *
     * @param Booking $booking
     * @return array
     */
    public function generateBookingQRCode(Booking $booking)
    {
        try {
            // Create verification data
            $qrData = [
                'booking_id' => $booking->id,
                'verification_code' => $this->generateVerificationCode($booking),
                'seat' => $booking->seat,
                'bus_name' => $booking->bus_name,
                'bus_number' => $booking->bus_number,
                'route' => $booking->source . ' to ' . $booking->destination,
                'price' => $booking->price,
                'booking_date' => $booking->created_at->format('Y-m-d H:i:s'),
                'payment_status' => $booking->payment_status,
                'verification_url' => route('booking.verify', ['code' => $this->generateVerificationCode($booking)])
            ];            // Convert to JSON string for QR code
            $qrString = json_encode($qrData);
              // Generate QR code
            $result = new Builder(
                writer: new SvgWriter(),
                data: $qrString,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin
            );
            
            $qrCode = $result->build();

            // Save QR code to storage
            $qrCodePath = 'qrcodes/booking_' . $booking->id . '.svg';
            $fullPath = storage_path('app/public/' . $qrCodePath);
            
            // Ensure directory exists
            $directory = dirname($fullPath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }            // Save the QR code
            file_put_contents($fullPath, $qrCode->getString());

            // Update booking with QR code path and verification code
            $booking->qr_code_path = $qrCodePath;
            $booking->verification_code = $this->generateVerificationCode($booking);
            $booking->save();

            Log::info('QR code generated for booking', [
                'booking_id' => $booking->id,
                'qr_code_path' => $qrCodePath
            ]);

            return [
                'success' => true,
                'qr_code_path' => $qrCodePath,
                'qr_code_url' => asset('storage/' . $qrCodePath),
                'verification_code' => $booking->verification_code,
                'qr_data' => $qrData
            ];

        } catch (\Exception $e) {
            Log::error('QR code generation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'QR code generation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate a unique verification code for booking
     *
     * @param Booking $booking
     * @return string
     */
    private function generateVerificationCode(Booking $booking)
    {
        // Create a unique code based on booking details
        $data = $booking->id . $booking->customer_id . $booking->seat . $booking->created_at->timestamp;
        return strtoupper(substr(md5($data), 0, 8));
    }

    /**
     * Verify booking using QR code data or verification code
     *
     * @param string $verificationCode
     * @return array
     */
    public function verifyBooking($verificationCode)
    {
        try {
            $booking = Booking::where('verification_code', $verificationCode)->first();

            if (!$booking) {
                return [
                    'success' => false,
                    'message' => 'Invalid verification code'
                ];
            }

            // Check if booking is valid
            if ($booking->payment_status !== 'completed') {
                return [
                    'success' => false,
                    'message' => 'Booking payment not completed',
                    'booking' => $booking
                ];
            }

            return [
                'success' => true,
                'message' => 'Booking verified successfully',
                'booking' => $booking,
                'verification_details' => [
                    'verified_at' => now()->format('Y-m-d H:i:s'),
                    'booking_id' => $booking->id,
                    'customer_name' => $booking->customer_name,
                    'seat' => $booking->seat,
                    'bus' => $booking->bus_name . ' (' . $booking->bus_number . ')',
                    'route' => $booking->source . ' to ' . $booking->destination,
                    'price' => $booking->price,
                    'booking_date' => $booking->created_at->format('Y-m-d H:i:s'),
                    'payment_status' => $booking->payment_status
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Booking verification failed', [
                'verification_code' => $verificationCode,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify booking from QR code JSON data
     *
     * @param string $qrData JSON string from QR code
     * @return array
     */
    public function verifyFromQRData($qrData)
    {
        try {
            $data = json_decode($qrData, true);

            if (!$data || !isset($data['verification_code'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid QR code data'
                ];
            }

            return $this->verifyBooking($data['verification_code']);

        } catch (\Exception $e) {
            Log::error('QR data verification failed', [
                'qr_data' => $qrData,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'QR data verification failed: ' . $e->getMessage()
            ];
        }
    }
}
