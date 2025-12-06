<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body class="bg-linear-to-br from-blue-100 to-blue-300 flex items-center justify-center h-screen">
    <main class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        <div class="flex justify-between">
            <h2 class="text-2xl font-bold text-blue-800 mb-6">Login to Your Account</h2>
            <a href="/" class="text-red-600 mb-6">Back</a>
        </div>


        <form action="{{ route('login') }}" method="POST" class="flex flex-col gap-4">
            @csrf

            <!-- Email -->
            <div class="flex flex-col">
                <label for="email" class="mb-1 font-semibold text-gray-700">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Password -->
            <div class="flex flex-col">
                <label for="password" class="mb-1 font-semibold text-gray-700">Password</label>
                <input type="password" name="password" id="password" required
                       class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit -->
            <button type="submit" 
                    class="bg-blue-600 text-white font-semibold py-2 rounded-lg shadow-md hover:bg-blue-700 transition duration-300 mt-2">
                Login
            </button>

            <a href="{{ route('password.request') }}" class="text-blue-600 font-semibold hover:underline">Forgot Password?</a>

            <!-- Errors -->
            @if ($errors->any())
                <ul class="mt-4 text-red-600 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </form>

        <p class="mt-4 text-center text-gray-600">
            Don't have an account? 
            <a href="{{ route('show.register') }}" class="text-blue-600 font-semibold hover:underline">Register</a>
        </p>
    </main>
</body>
</html>