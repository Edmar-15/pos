<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product | POS</title>
    @vite('resources/css/app.css', 'resources/js/app.js')
</head>
<body class="flex h-screen bg-gray-100">
    <aside class="sidebar" id="sidebar">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold" id="logo">PoS</h1>

            <button id="toggleBtn" class="text-white p-2">
                ☰
            </button>
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
            <a href="{{ route('pos.products') }}" class="menu-item active">
                <span class="icon"><i class="bi bi-boxes"></i></span>
                <span class="text">Products</span>
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
    <main class="flex-1 p-6 overflow-auto">
        <div class="main-header bg-white shadow p-4 rounded mb-6 flex items-center justify-between">
            @auth
                <div>
                    <h1 class="text-xl font-semibold">Welcome, {{ Auth::user()->name }}</h1>
                    <p class="text-sm text-gray-500">POS System Admin</p>
                </div>
            @endauth

            <img src="{{ asset('assets\logo.png') }}" 
                alt="Logo" 
                class="w-10 h-10 object-contain">
        </div>

        <div class="p-6 bg-white rounded shadow flex gap-6">
            <div class="w-1/3">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" 
                        alt="{{ $product->name }}"
                        class="w-full h-64 object-contain rounded border">
                @else
                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center rounded border">
                        No image available
                    </div>
                @endif
            </div>

            <div class="w-2/3 space-y-2">
                <h2 class="text-2xl font-bold">Product Detail</h2>
                <p><strong>Name:</strong> {{ $product->name }}</p>
                <p><strong>Category:</strong> {{ $product->category->name ?? 'N/A' }}</p>
                <p>
                    <strong>Stock:</strong> 
                    <span class="{{ $product->stock < 100 ? 'text-red-600 font-semibold' : '' }}">
                        {{ $product->stock }}
                    </span>
                </p>
                <p><strong>Sell Price:</strong> ₱{{ number_format($product->sell_price, 2) }}</p>
                <p><strong>Purchase Price:</strong> ₱{{ number_format($product->purchase_price, 2) }}</p>
                <p><strong>Created At:</strong> {{ $product->created_at->format('M d, Y H:i') }}</p>
                <p class="mb-3"><strong>Updated At:</strong> {{ $product->updated_at->format('M d, Y H:i') }}</p>
                <a href="{{ route('pos.products') }}" 
                   class="px-4 py-2 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
                    ← Back to Products
                </a>
            </div>

        </div>
    </main>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleBtn');
        const logo = document.getElementById('logo');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-collapsed');
            logo.classList.toggle('logoNone');
        });

        const dropdown = document.getElementById('dropdown');
        const droplist = document.getElementById('droplist');

        dropdown.addEventListener('click', () => {
            droplist.classList.toggle('shown');
        });
    </script>
</body>
</html>