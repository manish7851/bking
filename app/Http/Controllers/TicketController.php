<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Bus;
use App\Models\Route;
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

    
    public function trackBusRoute($bus_id, $active_route_id)
    {
        if (!session('customer_id')) {
            return redirect()->route('userlogin')
                ->with('error', 'Please login to view your bookings')
                ->with('redirect_after_login', url()->current());
        }

        $bus = Bus::find($bus_id);
        $route = Route::find($active_route_id);

        if ($bus && $route) {
            $source = $route->source;
            $destination = $route->destination;
            $imei = $bus->imei;
            $source_latitude = $route->source_latitude;
            $source_longitude = $route->source_longitude;
            $destination_latitude = $route->destination_latitude;
            $destination_longitude = $route->destination_longitude;
            
            return view('zone', [
                'source' => $source,
                'destination' => $destination,
                'imei' => $imei,
                'source_latitude' => $source_latitude,
                'source_longitude' => $source_longitude,
                'destination_latitude' => $destination_latitude,
                'destination_longitude' => $destination_longitude,
            ]);
           
        }
        return 'Invalid request';
    }
}
