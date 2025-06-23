<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bus Ticket #{{ $booking->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin: 0 auto; width: 80%; border: 1px solid #ccc; padding: 20px; border-radius: 8px; }
        .details th, .details td { padding: 6px 12px; }
        .qr { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bus Ticket</h2>
        <h4>Booking ID: {{ $booking->id }}</h4>
    </div>
    <table class="details">
        <tr><th>Passenger Name</th><td>{{ $booking->customer->customer_name ?? 'N/A' }}</td></tr>
        <tr><th>Bus Name</th><td>{{ $booking->bus_name }} ({{ $booking->bus_number }})</td></tr>
        <tr>
            <th>Route</th>
            <td>{{ trim(preg_replace('/[^\p{L}\p{N}\s→-]/u', '', $booking->route->source ?? $booking->source ?? 'N/A')) }} → {{ trim(preg_replace('/[^\p{L}\p{N}\s→-]/u', '', $booking->route->destination ?? $booking->destination ?? 'N/A')) }}</td>
        </tr>
        <tr><th>Seat</th><td>{{ $booking->seat }}</td></tr>
        <tr><th>Price</th><td>Rs. {{ number_format($booking->price, 2) }}</td></tr>
        <tr><th>Payment Status</th><td>{{ ucfirst($booking->payment_status) }}</td></tr>
        <tr><th>Booked At</th><td>{{ $booking->created_at->format('Y-m-d H:i') }}</td></tr>
    </table>
    @if($booking->qr_code_path)
    <div class="qr">
        <img src="{{ public_path('storage/' . $booking->qr_code_path) }}" alt="QR Code" width="120">
        <div style="font-size:12px; color:#888;">Scan to verify</div>
    </div>
    @endif
</body>
</html>
