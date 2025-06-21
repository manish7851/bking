<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Bus Ticket Details</title>
</head>
<body>
    <h2>Thank you for your booking!</h2>
    <strong>Ambikaswori Yatayat</strong>
    <p>Dear {{ $booking->user->name }},</p>
    <p>Here are your ticket details:</p>
    <ul>
        <li><strong>Bus Name:</strong> {{ $booking->bus_name }}</li>
        <li><strong>Bus Number:</strong> {{ $booking->bus_number }}</li>
        <li><strong>Route:</strong> {{ $booking->source }} to {{ $booking->destination }}</li>
        <li><strong>Seat Number:</strong> {{ $booking->seat }}</li>
        <li><strong>Price:</strong> {{ $booking->price }}</li>
        <li><strong>Booked Date:</strong> {{ $booking->created_at->format('d M Y, h:i A') }}</li>
    </ul>
    <p>We wish you a pleasant journey!</p>
</body>
</html>
