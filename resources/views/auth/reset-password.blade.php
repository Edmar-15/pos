<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body class="bg-linear-to-br from-blue-100 to-blue-300 flex items-center justify-center h-screen">
    <main class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        
        <h2 class="text-2xl font-bold text-blue-800 mb-6">Reset Password</h2>

        <form action="{{ route('password.update') }}" method="POST" class="flex flex-col gap-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="flex flex-col">
                <label class="font-semibold">Email</label>
                <input type="email" name="email" required
                    class="border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div class="flex flex-col">
                <label class="font-semibold">New Password</label>
                <input type="password" name="password" required
                    class="border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <div class="flex flex-col">
                <label class="font-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" required
                    class="border border-gray-300 rounded-lg px-3 py-2">
            </div>

            <button class="bg-blue-600 text-white p-2 rounded-lg hover:bg-blue-700">
                Reset Password
            </button>

            @if ($errors->any())
                <ul class="text-red-600 list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif

        </form>

        <p class="mt-4 text-center">
            <a href="{{ route('show.login') }}" class="text-blue-600 hover:underline">Back to login</a>
        </p>

    </main>
</body>
</html>
