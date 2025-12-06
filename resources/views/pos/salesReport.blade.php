<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS|Sales Report</title>
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
                <li class="menu-item active">
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

        <div class="p-4 bg-white rounded shadow">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 space-y-4 md:space-y-0">
                <h2 class="text-2xl font-bold">Sales Report</h2>

                <!-- Responsive date filter -->
                <div class="flex flex-wrap items-center gap-2">
                    <input type="date" id="startDate" class="border p-2 rounded w-full sm:w-auto" placeholder="Start Date">
                    <span class="hidden sm:inline">to</span>
                    <input type="date" id="endDate" class="border p-2 rounded w-full sm:w-auto" placeholder="End Date">
                    <button id="resetBtn" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500 transition">
                        Reset
                    </button>
                </div>
            </div>
            <div class="overflow-auto rounded-lg border">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 text-left">
                        <th class="p-3">Order No.</th>
                        <th class="p-3">Product Name</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">In Stock</th>
                        <th class="p-3">Sold</th>
                        <th class="p-3">Date</th>
                    </thead>
                    <tbody id="salesReportTableBody"></tbody>
                </table>
                <div id="paginationControls" class="flex justify-center mt-2 gap-2 py-2"></div>
            </div>
            
        </div>
    </main>

    <script>
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

        // Inputs
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        const resetBtn = document.getElementById('resetBtn');

        // PAGINATED fetch function
        function fetchSalesData(start = '', end = '', page = 1) {
            let url = "{{ route('sales.data') }}?page=" + page;
            const params = new URLSearchParams();

            if(start) params.append('start', start);
            if(end) params.append('end', end);

            url += "&" + params.toString();

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    const tableBody = document.getElementById('salesReportTableBody');
                    const pagination = document.getElementById('paginationControls');

                    tableBody.innerHTML = '';

                    data.data.forEach(sale => {
                        const row = document.createElement('tr');
                        row.classList.add('border-b');

                        row.innerHTML = `
                            <td class="p-3">${sale.ordernumber}</td>
                            <td class="p-3">${sale.productname}</td>
                            <td class="p-3">${sale.price}</td>
                            <td class="p-3">${sale.instock}</td>
                            <td class="p-3">${sale.sold}</td>
                            <td class="p-3">${sale.date}</td>
                        `;
                        tableBody.appendChild(row);
                    });

                    pagination.innerHTML = `
                        <button 
                            ${data.prev_page_url ? '' : 'disabled'}
                            onclick="fetchSalesData('${start}', '${end}', ${data.current_page - 1})"
                            class="px-4 py-2 bg-gray-300 rounded disabled:opacity-50">
                            Previous
                        </button>

                        <span class="px-4 py-2">Page ${data.current_page} of ${data.last_page}</span>

                        <button
                            ${data.next_page_url ? '' : 'disabled'}
                            onclick="fetchSalesData('${start}', '${end}', ${data.current_page + 1})"
                            class="px-4 py-2 bg-gray-300 rounded disabled:opacity-50">
                            Next
                        </button>
                    `;
                })
                .catch(err => console.error('Error:', err));
        }

        // Filter changes → go back to page 1
        [startDateInput, endDateInput].forEach(input => {
            input.addEventListener('input', () => {
                fetchSalesData(startDateInput.value, endDateInput.value, 1);
            });
        });

        // Reset filters
        resetBtn.addEventListener('click', () => {
            startDateInput.value = '';
            endDateInput.value = '';
            fetchSalesData('', '', 1);
        });

        // Initial load
        document.addEventListener('DOMContentLoaded', () => fetchSalesData());
    </script>
</body>
</html>