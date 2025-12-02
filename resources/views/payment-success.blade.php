<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Success</title>
</head>
<body>
    <h1>Payment Successful</h1>
    <p>Order: {{ $order->order_number ?? 'N/A' }}</p>
    <p>Thank you — the sale has been recorded.</p>
    <a href="{{ url('/') }}">Return to POS</a>
</body>
</html>