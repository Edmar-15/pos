<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="flex h-screen bg-gray-100">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold" id="logo">PoS</h1>
        <button id="toggleBtn" class="text-white p-2">☰</button>
    </div>

    <nav class="menu">
        <a href="{{ route('pos.index') }}" class="menu-item active">
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

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="p-4 bg-white rounded shadow">
            <p>Products <i>(low on stock)</i></p>
            <p>Items: <span id="lowStockCount">0</span></p>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <p>Daily Purchase</p>
            <p>Units: <span id="dailyPurchase">0</span></p>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <p>Monthly Sales</p>
            <p>₱ <span id="monthlySales">0.00</span></p>
        </div>
        <div class="p-4 bg-white rounded shadow">
            <p>Daily Sales</p>
            <p>₱ <span id="dailySales">0.00</span></p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 bg-white rounded shadow">
            <h2 class="text-lg font-semibold h-20 md:h-24">Monthly Sales by Product</h2>
            <canvas id="salesChart" height="100"></canvas>
        </div>
        <div class="p-4 bg-white rounded shadow w-98">
            <h2 class="text-lg font-semibold h-20 md:h-24">Low Stock Products Distribution</h2>
            <canvas id="lowStockChart" height="80"></canvas>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
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

    // Fetch dashboard data
    fetch("{{ route('dashboard.data') }}")
    .then(res => res.json())
    .then(data => {
        // Update cards
        document.getElementById('lowStockCount').textContent = data.lowStock;
        document.getElementById('dailyPurchase').textContent = data.dailyPurchase;
        document.getElementById('dailySales').textContent = parseFloat(data.dailySales).toFixed(2);
        document.getElementById('monthlySales').textContent = parseFloat(data.monthlySales).toFixed(2);

        // Bar chart - Monthly Sales
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: data.chart.labels,
                datasets: [{
                    label: 'Units Sold',
                    data: data.chart.data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Pie chart - Low Stock Products Distribution
        const lowStockCtx = document.getElementById('lowStockChart').getContext('2d');
        new Chart(lowStockCtx, {
            type: 'pie',
            data: {
                labels: data.lowStockProducts.map(p => p.name),
                datasets: [{
                    data: data.lowStockProducts.map(p => p.stock),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                
            }
        });
    })
    .catch(err => console.error('Error fetching dashboard data:', err));
});
</script>
</body>
</html>
