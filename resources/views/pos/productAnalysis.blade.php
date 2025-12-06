<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS | Product Info</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
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
            <a href="{{ route('pos.products') }}" class="menu-item">
                <span class="icon"><i class="bi bi-boxes"></i></span>
                <span class="text">Products</span>
            </a>
            <a href="{{ route('show.logs') }}" class="menu-item">
                <span class="icon"><i class="bi bi-clipboard"></i></span>
                <span class="text">Logs</span>
            </a>
            <button class="menu-item w-full text-left active" id="dropdown">
                <span class="icon"><i class="bi bi-graph-down"></i></span>
                <span class="text">Reports</span>
            </button>
            <ul class="menu shown" id="droplist">
                <li class="menu-item">
                    <a href="{{ route('pos.report.sales') }}">
                        <span class="icon"><i class="bi bi-graph-down"></i></span>
                        <span class="text">Sales Report</span>
                    </a>
                </li>
                <li class="menu-item active">
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

            <img src="{{ asset('assets/logo.png') }}" alt="Logo" class="w-10 h-10 object-contain">
        </div>

        <div class="p-4 bg-white rounded shadow">

            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">

                <h2 class="text-2xl font-bold">Product Info</h2>

                <!-- Search Bar -->
                <input 
                    type="text" 
                    id="searchInput"
                    placeholder="Search product or category..."
                    class="border p-2 rounded w-full md:w-64"
                >

                <!-- Low Stock Toggle -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="lowStockToggle" class="w-5 h-5">
                    <label for="lowStockToggle" class="text-gray-700">Show Low Stock</label>
                </div>
            </div>

            <div class="overflow-auto rounded-lg border">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 text-left">
                        <th class="p-3">Product Name</th>
                        <th class="p-3">Category Name</th>
                        <th class="p-3">Sold</th>
                        <th class="p-3">Remaining Stock</th>
                    </thead>
                    <tbody id="productInfoTableBody"></tbody>
                </table>
            </div>

        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    const logo = document.getElementById('logo');

    // Sidebar toggle
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');
        logo.classList.toggle('logoNone');
    });

    // Dropdown menu
    const dropdown = document.getElementById('dropdown');
    const droplist = document.getElementById('droplist');
    dropdown.addEventListener('click', () => {
        droplist.classList.toggle('hidden');
    });

    // Elements
    const lowStockToggle = document.getElementById('lowStockToggle');
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('productInfoTableBody');

    let allProducts = [];

    // Fetch Product Info
    function fetchProductInfo(lowStock = false) {
        let url = "{{ route('product.info.data') }}";
        if (lowStock) url += "?low_stock=1";

        fetch(url)
            .then(res => res.json())
            .then(data => {
                allProducts = data;
                renderTable(allProducts);
            })
            .catch(err => console.error("Error loading product info:", err));
    }

    // Render Table w/ Low Stock Border + Search
    function renderTable(products) {
        tableBody.innerHTML = "";

        products.forEach(product => {
            const isLowStock = product.remaining_stock > 0 && product.remaining_stock < 100; // threshold

            const row = document.createElement("tr");
            row.className = `border-b ${isLowStock ? 'border border-red-600 bg-red-50' : ''}`;

            row.innerHTML = `
                <td class="p-3">${product.productname}</td>
                <td class="p-3">${product.categoryname}</td>
                <td class="p-3">${product.sold}</td>
                <td class="p-3">${product.remaining_stock}</td>
            `;

            tableBody.appendChild(row);
        });
    }

    // Dynamic Search
    searchInput.addEventListener("input", () => {
        const term = searchInput.value.toLowerCase();

        const filtered = allProducts.filter(product =>
            product.productname.toLowerCase().includes(term) ||
            product.categoryname.toLowerCase().includes(term)
        );

        renderTable(filtered);
    });

    // Low Stock Toggle
    lowStockToggle.addEventListener("change", () => {
        fetchProductInfo(lowStockToggle.checked);
    });

    // Initial Load
    fetchProductInfo();
});
</script>

</body>
</html>
