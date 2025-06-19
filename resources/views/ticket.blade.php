<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .ticket {
            border: 1px solid #000;
            padding: 20px;
            width: 600px;
            margin: auto;
        }
        .qr-code {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="ticket">        <div class="company-header">
            <h1 style="text-align: center;">GHORAHI YATAYAT</h1>
            <h2 style="text-align: center;">Bus Ticket</h2>
        </div>
        <div class="ticket-details">
            <p><strong>Booking ID:</strong> {{ $booking->id }}</p>
            <p><strong>Bus Name:</strong> {{ $booking->bus_name }}</p>
            <p><strong>Bus Number:</strong> {{ $booking->bus_number }}</p>
            <p><strong>From:</strong> {{ $booking->source }}</p>
            <p><strong>To:</strong> {{ $booking->destination }}</p>
            <p><strong>Seat:</strong> {{ $booking->seat }}</p>
            <p><strong>Price:</strong> Rs. {{ number_format($booking->price, 2) }}</p>
            <p><strong>Date:</strong> {{ $booking->created_at->format('M d, Y h:i A') }}</p>
            <p><strong>Status:</strong> {{ $booking->status }}</p>
        </div>        <div class="qr-code">
            <p style="text-align: center;"><strong>Scan for verification</strong></p>
            @if($booking->qr_code_path && file_exists(storage_path('app/public/' . $booking->qr_code_path)))
                <img src="{{ public_path('storage/' . $booking->qr_code_path) }}" 
                     alt="Booking QR Code" 
                     style="width: 150px; height: 150px; margin: 0 auto; display: block;">
                @if($booking->verification_code)
                    <p style="text-align: center; margin-top: 10px; font-family: monospace; font-size: 14px;">
                        <strong>Code: {{ $booking->verification_code }}</strong>
                    </p>
                @endif
            @else
                <img src="https://api.qrserver.com/v1/create-qr-code/?data=GHORAHI YATAYAT - Ticket ID:{{ $booking->id }}, Bus:{{ $booking->bus_number }}, Seat:{{ $booking->seat }}&size=150x150" alt="QR Code">
            @endif
        </div>

        <div class="footer" style="margin-top: 20px; text-align: center; font-size: 0.9em;">
            <p>Thank you for traveling with GHORAHI YATAYAT</p>
            <p>For inquiries, please call: +977 XXXXXXXXXX</p>
        </div>
    </div>
</body>
</html>
