<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS|Category</title>
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
            <a href="{{ route('pos.category') }}" class="menu-item active">
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
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold">Manage Categories</h2>

                <a href="{{ route('show.create.category') }}" 
                   class="px-4 py-2 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
                    <i class="bi bi-plus-lg"></i> Add Category
                </a>
            </div>
            <div class="overflow-auto rounded-lg border">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 text-left">
                        <tr>
                            <th class="p-3">No.</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Created At</th>
                            <th class="p-3">Updated At</th>
                            <th class="p-3 text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="categoryTableBody"></tbody>
                </table>
            </div>
        </div>
    </main>
</body>
<script>
    function loadCategories() {
        fetch("/api/categories")
            .then(res => res.json())
            .then(categories => {
                // Merge newly added categories
                let newCats = JSON.parse(sessionStorage.getItem('newCategories')) || [];
                categories = [...categories, ...newCats];

                // Merge edited categories
                let editedCats = JSON.parse(sessionStorage.getItem('editedCategories')) || [];
                editedCats.forEach(edited => {
                    const index = categories.findIndex(c => c.id === edited.id);
                    if (index !== -1) categories[index] = edited;
                });

                let tbody = document.getElementById('categoryTableBody');
                tbody.innerHTML = "";

                if (categories.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center p-4 text-gray-500">No categories found</td></tr>`;
                    return;
                }

                categories.forEach((cat, index) => {
                    tbody.innerHTML += `
                        <tr id="catRow${cat.id}">
                            <td class="p-3">${index + 1}</td>
                            <td class="p-3">${cat.name}</td>
                            <td class="p-3">${new Date(cat.created_at).toLocaleString()}</td>
                            <td class="p-3">${new Date(cat.updated_at).toLocaleString()}</td>
                            <td class="p-3 flex gap-2 justify-center">
                                <a href="/category/${cat.id}/edit" class="text-blue-600 hover:underline">Edit</a>
                                <button onclick="deleteCategory(${cat.id})" class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    `;
                });

                // Clear sessionStorage after merging
                sessionStorage.removeItem('newCategories');
                sessionStorage.removeItem('editedCategories');
            });
    }

    loadCategories();

    function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category?')) return;

        fetch(`/api/categories/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            let row = document.getElementById('catRow' + id);
            if (row) row.remove();
            reloadIndexes();
        });
    }

    // Update row numbers
    function reloadIndexes() {
        let tbody = document.getElementById('categoryTableBody');
        Array.from(tbody.rows).forEach((row, i) => row.cells[0].innerText = i + 1);
    }

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
</html>