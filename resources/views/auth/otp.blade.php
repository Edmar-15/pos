<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - OTP Verification</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body class="bg-linear-to-br from-blue-100 to-blue-300 flex items-center justify-center h-screen">
    <main class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        
        <div class="flex justify-between">
            <h2 class="text-2xl font-bold text-blue-800 mb-6">Verify OTP</h2>
            <a href="{{ route('show.login') }}" class="text-red-600 mb-6">Back</a>
        </div>

        <p class="text-gray-700 mb-4">
            An OTP has been sent to <span class="font-semibold">{{ $email }}</span>.
            Please enter it below to continue.
        </p>

        <form action="{{ route('otp.verify') }}" method="POST" class="flex flex-col gap-4">
            @csrf

            <input type="hidden" name="email" value="{{ $email }}">

            <!-- OTP Input -->
            <div class="flex flex-col">
                <label for="otp" class="mb-1 font-semibold text-gray-700">OTP Code</label>
                <input type="text" name="otp" id="otp" maxlength="6" required
                    class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit -->
            <button type="submit" 
                class="bg-blue-600 text-white font-semibold py-2 rounded-lg shadow-md hover:bg-blue-700 transition duration-300 mt-2">
                Verify OTP
            </button>

            @if ($errors->any())
                <ul class="mt-4 text-red-600 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </form>

    </main>
</body>
</html>
