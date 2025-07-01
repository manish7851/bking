<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Send a test email.
     */
    public function sendTestEmail(Request $request)
    {
        $to = $request->input('to', 'example@example.com');
        $subject = 'Test Email from Bus Booking';
        $message = 'This is a test email sent from the Bus Booking application.';

        Mail::to($message, function ($mail) use ($to, $subject) {
            $mail->to($to)
                 ->subject($subject);
        });

        return response()->json(['message' => 'Test email sent to ' . $to]);
    }
}
