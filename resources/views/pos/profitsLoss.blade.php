<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | Profits & Loss</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
</head>
<body class="flex h-screen bg-gray-100">
    <aside class="sidebar" id="sidebar">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold" id="logo">PoS</h1>
            <button id="toggleBtn" class="text-white p-2">☰</button>
        </div>
        <nav class="menu">
            <a href="{{ route('pos.index') }}" class="menu-item"><span class="icon"><i class="bi bi-house"></i></span> Dashboard</a>
            <a href="{{ route('pos.category') }}" class="menu-item"><span class="icon"><i class="bi bi-tags"></i></span> Category</a>
            <a href="{{ route('pos.products') }}" class="menu-item"><span class="icon"><i class="bi bi-boxes"></i></span> Products</a>
            <a href="{{ route('show.logs') }}" class="menu-item">
                <span class="icon"><i class="bi bi-clipboard"></i></span>
                <span class="text">Logs</span>
            </a>
            <button class="menu-item w-full text-left active" id="dropdown">
                <span class="icon"><i class="bi bi-graph-down"></i></span> Reports
            </button>
            <ul class="menu shown" id="droplist">
                <li class="menu-item"><a href="{{ route('pos.report.sales') }}"><span class="icon"><i class="bi bi-graph-down"></i></span> Sales Report</a></li>
                <li class="menu-item"><a href="{{ route('pos.report.product.analysis') }}"><span class="icon"><i class="bi bi-graph-down"></i></span> Product Info</a></li>
                <li class="menu-item active"><a href="{{ route('pos.report.profits') }}"><span class="icon"><i class="bi bi-graph-down"></i></span> Profits & Loss</a></li>
            </ul>
        </nav>
        <form action="{{ route('logout') }}" method="POST" class="mt-auto">
            @csrf
            <button type="submit" class="menu-item danger w-full text-left">
                <span class="icon"><i class="bi bi-box-arrow-left"></i></span> Logout
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
            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
        </div>

        <div class="p-4 bg-white rounded shadow">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 space-y-4 md:space-y-0">
                <h2 class="text-2xl font-bold">Product Info</h2>
            </div>

            <div class="overflow-auto rounded-lg border">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 text-left">
                        <th class="p-3">Product Name</th>
                        <th class="p-3">Sell Price</th>
                        <th class="p-3">Units Sold</th>
                        <th class="p-3">Total Profit</th>
                    </thead>
                    <tbody id="profitLossTableBody"></tbody>
                </table>
            </div>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    const logo = document.getElementById('logo');
    const dropdown = document.getElementById('dropdown');
    const droplist = document.getElementById('droplist');
    const tableBody = document.getElementById('profitLossTableBody');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');
        logo.classList.toggle('logoNone');
    });

    dropdown.addEventListener('click', () => {
        droplist.classList.toggle('hidden');
    });

    // Fetch profits & loss data
    function fetchProfitLossData() {
        fetch("{{ route('profit.loss.data') }}") // Make sure this route returns JSON
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = '';

            data.forEach(item => {
                const row = document.createElement('tr');
                row.classList.add('border-b');

                // Total profit = (sell_price - purchase_price) * units_sold
                const totalProfit = ((item.sell_price - item.purchase_price) * item.sold).toFixed(2);

                row.innerHTML = `
                    <td class="p-3">${item.productname}</td>
                    <td class="p-3">${item.sell_price}</td>
                    <td class="p-3">${item.sold}</td>
                    <td class="p-3">${totalProfit}</td>
                `;

                tableBody.appendChild(row);
            });
        })
        .catch(err => console.error('Error fetching profit & loss data:', err));
    }

    // Initial load
    fetchProfitLossData();
});
</script>
</body>
</html>
