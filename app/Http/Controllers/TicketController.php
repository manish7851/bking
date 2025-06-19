<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    /**
     * Download the ticket as PDF
     */
    public function download($id)
    {
        $booking = Booking::with(['route', 'customer'])->findOrFail($id);
        if (!session('customer_id') || $booking->customer_id !== session('customer_id')) {
            abort(403, 'Unauthorized');
        }
        // Render a simple PDF view (create this if not exists)
        $pdf = Pdf::loadView('booking.ticket_pdf', compact('booking'));
        return $pdf->download('ticket_'.$booking->id.'.pdf');
    }
}
