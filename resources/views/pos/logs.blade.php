<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | Logs</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body class="flex h-screen bg-gray-100">

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold" id="logo">PoS</h1>
            <button id="toggleBtn" class="text-white p-2">☰</button>
        </div>

        <nav class="menu">
            <a href="{{ route('pos.index') }}" class="menu-item">
                <span class="icon"><i class="bi bi-house"></i></span>
                <span class="text">Dashboard</span>
            </a>
            <a href="{{ route('pos.category') }}" class="menu-item">
                <span class="icon"><i class="bi bi-tags"></i></span>
                <span class="text">Category</span>
            </a>
            <a href="{{ route('pos.products') }}" class="menu-item">
                <span class="icon"><i class="bi bi-boxes"></i></span>
                <span class="text">Products</span>
            </a>
            <a href="{{ route('show.logs') }}" class="menu-item active">
                <span class="icon"><i class="bi bi-clipboard"></i></span>
                <span class="text">Logs</span>
            </a>
            <button class="menu-item w-full text-left" id="dropdown">
                <span class="icon"><i class="bi bi-graph-down"></i></span>
                <span class="text">Reports</span>
            </button>
            <ul class="menu hidden" id="droplist">
                <li class="menu-item">
                    <a href="{{ route('pos.report.sales') }}">
                        <span class="icon"><i class="bi bi-graph-down"></i></span>
                        <span class="text">Sales Report</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('pos.report.product.analysis') }}">
                        <span class="icon"><i class="bi bi-graph-down"></i></span>
                        <span class="text">Product Info</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="{{ route('pos.report.profits') }}">
                        <span class="icon"><i class="bi bi-graph-down"></i></span>
                        <span class="text">Profits & Loss</span>
                    </a>
                </li>
            </ul>
        </nav>

        <form action="{{ route('logout') }}" method="POST" class="mt-auto">
            @csrf
            <button type="submit" class="menu-item danger w-full text-left">
                <span class="icon"><i class="bi bi-box-arrow-left"></i></span>
                <span class="text">Logout</span>
            </button>
        </form>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6 overflow-auto">
        <div class="main-header bg-white shadow p-4 rounded mb-6 flex items-center justify-between">
            @auth
            <div>
                <h1 class="text-xl font-semibold">Welcome, {{ Auth::user()->name }}</h1>
                <p class="text-sm text-gray-500">POS System Admin</p>
            </div>
            @endauth
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
        </div>

        <div class="p-4 bg-white rounded shadow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">User Logs</h2>
            </div>
            <div class="overflow-auto rounded-lg border">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 text-left">
                        <th class="p-3">Name</th>
                        <th class="p-3">Time In</th>
                        <th class="p-3">Time out</th>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="border-b">
                                <td class="p-3">{{ $log->name }}</td>
                                <td class="p-3">{{ $log->time_in ?? '-' }}</td>
                                <td class="p-3">
                                    {{ $log->time_out ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
        </div>
    </main>
    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const logo = document.getElementById('logo');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            logo.classList.toggle('logoNone');
        });

        // Dropdown
        const dropdown = document.getElementById('dropdown');
        const droplist = document.getElementById('droplist');
        dropdown.addEventListener('click', () => droplist.classList.toggle('shown'));
    </script>
</body>
</html>