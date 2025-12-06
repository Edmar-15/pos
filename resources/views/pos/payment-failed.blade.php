<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Failed</title>
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body>
    <h1>Payment Failed or Canceled</h1>
    <p>Order: {{ $orderId ?? 'N/A' }}</p>
    <p>The payment was not completed. You may try again.</p>
    <a href="{{ url('/') }}">Return to POS</a>
</body>
</html>