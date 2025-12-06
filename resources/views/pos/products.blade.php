<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS|Products</title>
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
            <a href="{{ route('pos.products') }}" class="menu-item active">
                <span class="icon"><i class="bi bi-boxes"></i></span>
                <span class="text">Products</span>
            </a>
            <a href="{{ route('show.logs') }}" class="menu-item">
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
                <h2 class="text-2xl font-bold">Manage Products</h2>

                <a href="{{ route('show.create.product') }}" 
                   class="px-4 py-2 bg-sky-600 text-white rounded hover:bg-sky-700 transition">
                    <i class="bi bi-plus-lg"></i> Add Product
                </a>
            </div>
            <div class="overflow-auto rounded-lg border">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-200 text-left">
                        <th class="p-3">Product Name</th>
                        <th class="p-3">Preview</th>
                        <th class="p-3">Price</th>
                        <th class="p-3">Stock</th>
                        <th class="p-3 text-center">Actions</th>
                    </thead>
                    <tbody id="productTableBody"></tbody>
                </table>
            </div>
            
        </div>
    </main>

<script>
    function loadProducts() {
        fetch("/api/products")
            .then(res => res.json())
            .then(products => {
                // Merge newly added products from sessionStorage
                let newProds = JSON.parse(sessionStorage.getItem('newProducts')) || [];
                newProds = newProds.filter(p => p && p.id); // sanitize
                products = [...products, ...newProds];

                // Merge edited products from sessionStorage
                let editedProds = JSON.parse(sessionStorage.getItem('editedProducts')) || [];
                editedProds = editedProds.filter(p => p && p.id); // sanitize
                editedProds.forEach(edited => {
                    const index = products.findIndex(p => p.id === edited.id);
                    if (index !== -1) products[index] = edited;
                });

                let tbody = document.getElementById('productTableBody');
                tbody.innerHTML = "";

                if (products.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="text-center p-4 text-gray-500">No products found</td></tr>`;
                    return;
                }

                products.forEach(prod => {
                    tbody.innerHTML += `
                        <tr id="prodRow${prod.id}">
                            <td class="p-3">${prod.name}</td>
                            <td class="p-3">${prod.image ? `<img src="/storage/${prod.image}" class="w-12 h-12 object-cover rounded">` : 'No Image'}</td>
                            <td class="p-3">₱${prod.sell_price}</td>
                            <td class="p-3 ${prod.stock < 100 ? 'text-red-600 font-semibold' : ''}">${prod.stock}</td>
                            <td class="p-3 flex gap-2 justify-center">
                                <a href="{{ url('/products/show') }}/${prod.id}" class="hover:underline">View</a>
                                <a href="{{ url('/products') }}/${prod.id}/edit" class="text-blue-600 hover:underline">Edit</a>
                                <button onclick="deleteProduct(${prod.id})" class="text-red-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    `;
                });

                // Clear sessionStorage only for old items, keep new/edited for next operations
                sessionStorage.setItem('newProducts', JSON.stringify(newProds));
                sessionStorage.setItem('editedProducts', JSON.stringify(editedProds));
            })
            .catch(err => console.error('Error loading products:', err));
    }

    function deleteProduct(id) {
        if (!confirm('Are you sure you want to delete this product?')) return;

        fetch(`/api/products/${id}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || 'Product deleted successfully');

            // Remove deleted product from table
            let row = document.getElementById('prodRow' + id);
            if (row) row.remove();

            // Remove deleted product from sessionStorage
            let newProds = JSON.parse(sessionStorage.getItem('newProducts')) || [];
            newProds = newProds.filter(p => p.id !== id);
            sessionStorage.setItem('newProducts', JSON.stringify(newProds));

            let editedProds = JSON.parse(sessionStorage.getItem('editedProducts')) || [];
            editedProds = editedProds.filter(p => p.id !== id);
            sessionStorage.setItem('editedProducts', JSON.stringify(editedProds));
        })
        .catch(err => console.error('Error deleting product:', err));
    }

    // Initial load
    loadProducts();

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