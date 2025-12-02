<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex items-center justify-center min-h-screen bg-green-100">
    <div class="bg-white rounded-xl p-8 shadow-md text-center">
        <h1 class="text-3xl font-bold text-green-700 mb-4">Payment Successful!</h1>
        
        <p class="text-gray-700 mb-6">Thank you for your purchase. Your order has been successfully processed.</p>
        <a href="#" onclick="closeTab()" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
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
