<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | POS</title>
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

        <div class="p-6 bg-white rounded shadow mx-auto">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold">Edit Product</h2>
                <a href="{{ route('pos.products') }}" class="block text-blue-600 hover:underline text-center">
                        ← Back to Products
                    </a>
            </div>

            <form id="editProductForm" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @csrf

                <div class="space-y-3">
                    <label class="block font-medium">Product Image:</label>

                    <div class="w-full h-64 bg-gray-100 border rounded flex items-center justify-center overflow-hidden">
                        @if($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}"
                                alt="Product Image"
                                class="w-full h-full object-contain" id="previewImage">
                        @else
                            <span class="text-gray-500">No image available</span>
                        @endif
                    </div>

                    <input type="file" name="image" accept="image/*"
                        class="w-full mt-2 p-2 border rounded bg-white"
                        onchange="preview(event)">
                </div>

                <div class="space-y-4">

                    <div>
                        <label class="block font-medium mb-1">Category:</label>
                        <select name="category_id" class="w-full p-2 border rounded bg-white">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Product Name:</label>
                        <input type="text" name="name" value="{{ $product->name }}"
                            class="w-full p-2 border rounded bg-white">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Stock:</label>
                        <input type="number" min="0" name="stock" value="{{ $product->stock }}"
                            class="w-full p-2 border rounded bg-white">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Sell Price:</label>
                        <input type="number" step="0.01" name="sell_price" value="{{ $product->sell_price }}"
                            class="w-full p-2 border rounded bg-white">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Purchase Price:</label>
                        <input type="number" step="0.01" name="purchase_price" value="{{ $product->purchase_price }}"
                            class="w-full p-2 border rounded bg-white">
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 mt-4">
                        Update Product
                    </button>
                </div>
            </form>
        </div>

<script>
document.getElementById('editProductForm').addEventListener('submit', function(e) {
    e.preventDefault();

    let form = new FormData(this);
    form.append('_method', 'PUT');

    fetch("/api/products/{{ $product->id }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: form
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);

        let editedProducts = JSON.parse(sessionStorage.getItem('editedProducts')) || [];
        editedProducts = editedProducts.filter(p => p.id !== data.product.id);
        editedProducts.push(data.product);
        sessionStorage.setItem('editedProducts', JSON.stringify(editedProducts));

        window.location.href = "{{ route('pos.products') }}";
    })
    .catch(err => {
        console.error(err);
        alert("Error updating product. Make sure all required fields are correct.");
    });
});

function preview(event) {
    const output = document.getElementById('previewImage');
    
    if (!output) {
        const img = document.createElement("img");
        img.id = "previewImage";
        img.className = "w-full h-full object-cover";
        event.target.previousElementSibling.innerHTML = "";
        event.target.previousElementSibling.appendChild(img);
        output = img;
    }

    output.src = URL.createObjectURL(event.target.files[0]);
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
</body>
</html>