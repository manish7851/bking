<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Send a test email via Web or API (JSON)
     */
    public function sendTestEmail(Request $request)
    {
        $to = $request->input('to', 'example@example.com');
        $subject = 'Test Email from Bus Booking';
        $messageBody = 'This is a test email sent from the Bus Booking application.';

        try {
            Mail::raw($messageBody, function ($mail) use ($to, $subject) {
                $mail->to($to)
                     ->subject($subject);
            });

            // JSON request
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Test email sent to {$to}"
                ], 200);
            }

            // Web request
            return redirect()->back()->with('success', "Test email sent to {$to}");

        } catch (\Exception $e) {
            // JSON request error
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to send email: " . $e->getMessage()
                ], 500);
            }

            // Web request error
            return redirect()->back()->with('error', "Failed to send email: " . $e->getMessage());
        }
    }
}
