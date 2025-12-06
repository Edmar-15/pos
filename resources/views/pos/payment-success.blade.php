<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    @vite(['resources/css/app.css'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body class="flex items-center justify-center min-h-screen bg-green-100">

    <div class="bg-white rounded-xl p-8 shadow-md text-center max-w-lg w-full">

        <h1 class="text-3xl font-bold text-green-700 mb-4">Payment Successful!</h1>

        <p class="text-gray-700 mb-6">{{ $message ?? '' }}</p>

        @if(isset($order))
            @php
                // Decode cart_items if it's a JSON string
                $decodedItems = is_string($order->cart_items) ? json_decode($order->cart_items, true) : $order->cart_items;
            @endphp

            <h2 class="text-xl font-semibold text-gray-800 mb-3">Order #{{ $order->id }}</h2>

            <div class="text-left mb-6">
                <ul class="space-y-2">
                    @foreach($decodedItems as $item)
                        <li class="border-b pb-2">
                            <strong>{{ $item['name'] }}</strong><br>
                            ₱{{ number_format($item['sell_price'], 2) }} × {{ $item['quantity'] }}
                        </li>
                    @endforeach
                </ul>

                <p class="text-lg font-bold mt-4">
                    Total: ₱{{ number_format($order->total_amount, 2) }}
                </p>
            </div>

            <a href="{{ $receiptUrl }}"
            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition block mb-4">
                Download Receipt (PDF)
            </a>
        @endif


        <a href="#" onclick="closeTab()"
           class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
            Back to POS
        </a>

    </div>

    <script>
        function closeTab() {
            window.close();
        }
    </script>

</body>
</html>
