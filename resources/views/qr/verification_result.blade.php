<!DOCTYPE html>
<html>
<head>
    <title>Verification Result</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .ticket-info { background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0; }
        .status-badge { display: inline-block; padding: 5px 15px; border-radius: 15px; font-size: 14px; font-weight: bold; }
        .status-confirmed { background-color: #28a745; color: white; }
        .status-pending { background-color: #ffc107; color: black; }
        .status-cancelled { background-color: #dc3545; color: white; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .label { font-weight: bold; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .back-btn { background-color: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Ticket Verification Result</h2>
        
        @if(isset($error) || !$booking)
            <div class="alert alert-danger">
                <h4>❌ Verification Failed</h4>
                <p>{{ $error ?? 'Invalid QR code. Booking not found.' }}</p>
            </div>
        @else
            <div style="text-align: center; margin-bottom: 30px;">
                <h3>✅ Ticket Verified Successfully</h3>
            </div>
            
            <div class="ticket-info">
                <h4>Booking Details</h4>
                
                <div class="info-row">
                    <span class="label">Booking ID:</span>
                    <span>{{ $booking->id }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Passenger Name:</span>
                    <span>{{ $booking->customer->name ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Contact:</span>
                    <span>{{ $booking->customer->phone ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Bus Number:</span>
                    <span>{{ $booking->bus_number }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Route:</span>
                    <span>{{ $booking->route->from ?? 'N/A' }} → {{ $booking->route->to ?? 'N/A' }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Departure Date:</span>
                    <span>{{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Departure Time:</span>
                    <span>{{ \Carbon\Carbon::parse($booking->departure_time)->format('g:i A') }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Seat Number:</span>
                    <span>{{ $booking->seat_number }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Total Amount:</span>
                    <span>Rs. {{ number_format($booking->total_amount, 2) }}</span>
                </div>
                
                <div class="info-row">
                    <span class="label">Status:</span>
                    <span class="status-badge status-{{ $booking->status }}">
                        {{ ucfirst($booking->status) }}
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="label">Payment Status:</span>
                    <span class="status-badge status-{{ $booking->payment_status }}">
                        {{ ucfirst($booking->payment_status) }}
                    </span>
                </div>
                
                <div class="info-row">
                    <span class="label">Booking Date:</span>
                    <span>{{ $booking->created_at->format('M d, Y g:i A') }}</span>
                </div>
            </div>
        @endif
        
        <a href="{{ route('booking.verify.form') }}" class="back-btn">← Verify Another Ticket</a>
    </div>
</body>
</html>
