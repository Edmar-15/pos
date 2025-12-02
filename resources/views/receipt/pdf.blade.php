<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; }
    </style>
</head>
<body>

    <div class="center bold">POS RECEIPT</div>
    <div class="center">{{ now()->format('F d, Y h:i A') }}</div>
    <div class="line"></div>

    <p><strong>Order ID:</strong> {{ $order->id }}</p>
    <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
    <div class="line"></div>

    <table>
        @foreach($items as $item)
        <tr>
            <td>{{ $item['name'] }} x {{ $item['qty'] }}</td>
            <td style="text-align:right;">
                ₱{{ number_format($item['price'] * $item['qty'], 2) }}
            </td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>
    <p class="bold">TOTAL: ₱{{ number_format($order->total_amount, 2) }}</p>
    <div class="line"></div>

    <div class="center">Thank you for your purchase!</div>

</body>
</html>
