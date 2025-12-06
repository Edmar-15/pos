<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System - Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body class="bg-linear-to-br from-blue-100 to-blue-300 flex items-center justify-center h-screen">
    <main class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
        <div class="flex justify-between">
            <h2 class="text-2xl font-bold text-blue-800 text-center mb-6">Register an Account</h2>
            <a href="/" class="text-red-600 mb-6">Back</a>
        </div>

        <form action="{{ route('register') }}" method="POST" class="flex flex-col gap-4">
            @csrf

            <!-- Name -->
            <div class="flex flex-col">
                <label for="name" class="mb-1 font-semibold text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

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

            <!-- Confirm Password -->
            <div class="flex flex-col">
                <label for="password_confirmation" class="mb-1 font-semibold text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Submit -->
            <button type="submit"
                    class="bg-blue-600 text-white font-semibold py-2 rounded-lg shadow-md hover:bg-blue-700 transition duration-300 mt-2">
                Register
            </button>

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
            Already have an account? 
            <a href="{{ route('show.login') }}" class="text-blue-600 font-semibold hover:underline">Login</a>
        </p>
    </main>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const nameInput = document.getElementById("name");
            const emailInput = document.getElementById("email");
            const passInput = document.getElementById("password");
            const confirmInput = document.getElementById("password_confirmation");

            const validators = {
                name: value => value.trim().length > 0 ? "" : "Name is required.",
                email: value => {
                    if (!value) return "Email is required.";
                    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return regex.test(value) ? "" : "Invalid email format.";
                },
                password: value => value.length >= 8 ? "" : "Password must be at least 8 characters.",
                password_confirmation: (value) =>
                    value === passInput.value ? "" : "Passwords do not match."
            };

            function showError(input, message) {
                let existing = input.parentNode.querySelector(".error-text");
                if (existing) existing.remove();

                if (message) {
                    input.classList.add("border-red-500");

                    let small = document.createElement("small");
                    small.classList.add("text-red-600", "error-text");
                    small.innerText = message;
                    input.parentNode.appendChild(small);
                } else {
                    input.classList.remove("border-red-500");
                }
            }

            function validateInput(input) {
                const name = input.name;
                const message = validators[name](input.value);
                showError(input, message);
            }

            [nameInput, emailInput, passInput, confirmInput].forEach(input => {
                input.addEventListener("input", () => validateInput(input));
            });

        });
    </script>
</body>
</html>