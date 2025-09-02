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
     * Download the ticket as PDF (Web + JSON)
     */
    public function download(Request $request, $id)
    {
        $booking = Booking::with(['route', 'customer'])->findOrFail($id);

        if (!session('customer_id') || $booking->customer_id !== session('customer_id')) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        }

        $pdf = Pdf::loadView('booking.ticket_pdf', compact('booking'));

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'PDF ready for download',
                'file_name' => 'ticket_'.$booking->id.'.pdf'
            ]);
        }

        return $pdf->download('ticket_'.$booking->id.'.pdf');
    }

    /**
     * Track bus route (Web + JSON)
     */
    public function trackBusRoute(Request $request, $bus_id, $active_route_id)
    {
        if (!session('customer_id')) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to view your bookings',
                    'redirect_url' => route('userlogin')
                ], 401);
            }
            return redirect()->route('userlogin')
                ->with('error', 'Please login to view your bookings')
                ->with('redirect_after_login', url()->current());
        }

        $bus = Bus::find($bus_id);
        $route = Route::find($active_route_id);

        if (!$bus || !$route) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
            }
            return 'Invalid request';
        }

        $data = [
            'source' => $route->source,
            'destination' => $route->destination,
            'imei' => $bus->imei,
            'source_latitude' => $route->source_latitude,
            'source_longitude' => $route->source_longitude,
            'destination_latitude' => $route->destination_latitude,
            'destination_longitude' => $route->destination_longitude,
        ];

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('zone', $data);
    }
}
