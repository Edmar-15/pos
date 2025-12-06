<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="shortcut icon" href="{{ asset('assets/image.png') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="min-h-screen flex flex-col bg-linear-to-br from-blue-100 to-blue-300 overflow-hidden">

    <!-- NAVBAR -->
    <nav class="w-full bg-white shadow-md py-4 px-6 flex items-center justify-between shrink-0">
        <h1 class="text-2xl font-extrabold text-blue-800">
            POS-INVENTORY SYSTEM
        </h1>

        <div class="flex gap-4">
            <a href="{{ route('show.register') }}" 
               class="bg-blue-600 text-white font-semibold px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                Register
            </a>

            <a href="{{ route('show.login') }}" 
               class="border border-blue-600 text-blue-600 font-semibold px-4 py-2 rounded-lg hover:bg-blue-50 transition">
                Login
            </a>
        </div>
    </nav>

    <main class="flex grow overflow-hidden px-6 py-4 gap-6">

        <!-- LEFT PANEL -->
        <div class="flex-[2_2_0%] flex flex-col gap-4 overflow-auto bg-white p-6 rounded-xl" style="min-height:0;">
            <input type="text" id="search-bar" placeholder="Search"
                class="w-full p-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400"/>

            <button id="back-to-categories"
                    class="w-full p-3 mt-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition"
                    style="display:none;">
                Back to Categories
            </button>

            <div id="category-tiles" class="grid grid-cols-2 gap-4 mt-2">
                @foreach($categories as $category)
                    <div class="category-tile bg-white border border-gray-700 rounded-xl p-6 flex items-center justify-center font-semibold text-gray-800 cursor-pointer hover:bg-blue-50 transition"
                         data-id="{{ $category->id }}">
                        {{ $category->name }}
                    </div>
                @endforeach
            </div>

            <div id="product-grid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4 mt-4" style="display:none;">
            </div>
        </div>

        <!-- RIGHT PANEL: Cart -->
        <div class="flex-1 bg-white rounded-xl p-6 flex flex-col overflow-auto">
            <div class="flex items-center gap-2 mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 7M7 13l-2 5m5-5v5m4-5v5m4-5l2 5"/>
                </svg>
                <h2 class="text-xl font-bold">Cart</h2>
            </div>

            <div class="mb-6">
                <h3 class="font-semibold mb-2">Payment Method</h3>
                <select id="payment-method" class="w-full p-2 rounded-lg text-gray-800">
                    <option value="cash">Cash</option>
                    <option value="paymongo">PayMongo (Test Payment)</option>
                </select>
            </div>

            <div class="flex-1 overflow-auto mb-6">
                <h3 class="font-semibold mb-2">Items</h3>
                <div class="space-y-3" id="cart-items"></div>
            </div>

            <div class="mb-6 space-y-1">
                <p id="subtotal">Subtotal: Php 0</p>
                <p id="tax">Tax: 10% (Php 0)</p>
                <p id="total" class="text-lg font-bold">Total: Php 0</p>
            </div>

            <button id="complete-order-btn" class="w-full bg-sky-600 py-3 rounded-lg font-semibold hover:bg-sky-700 transition">
                Complete Order
            </button>
        </div>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBar = document.getElementById('search-bar');
            const categoryTiles = document.getElementById('category-tiles');
            const productGrid = document.getElementById('product-grid');
            const backBtn = document.getElementById('back-to-categories');
            const cartItemsDiv = document.getElementById('cart-items');
            const subtotalEl = document.getElementById('subtotal');
            const taxEl = document.getElementById('tax');
            const totalEl = document.getElementById('total');
            const paymentMethod = document.getElementById('payment-method');
            const completeBtn = document.getElementById('complete-order-btn');

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const baseUrl = "{{ url('/') }}";
            const cart = {};
            let currentProducts = [];
            let selectedCategoryId = null;

            function renderCart() {
                cartItemsDiv.innerHTML = '';
                let subtotal = 0;

                Object.values(cart).forEach(item => {
                    const div = document.createElement('div');
                    div.className = "flex items-center justify-between border border-blue-600 bg-white/10 p-3 rounded-lg";

                    div.innerHTML = `
                        <div>
                            <p class="font-medium">${item.name}</p>
                            <p class="text-sm">Php ${item.sell_price}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button class="bg-sky-600 px-2 rounded quantity-btn" data-id="${item.id}" data-action="add">+</button>
                            <span>${item.quantity}</span>
                            <button class="bg-sky-600 px-2 rounded quantity-btn" data-id="${item.id}" data-action="subtract">-</button>
                            <button class="bg-red-500 px-2 rounded text-white font-bold delete-btn" data-id="${item.id}">D</button>
                        </div>
                    `;
                    cartItemsDiv.appendChild(div);
                    subtotal += item.sell_price * item.quantity;
                });

                const tax = Math.round(subtotal * 0.1);
                const total = subtotal + tax;

                subtotalEl.innerText = `Subtotal: Php ${subtotal}`;
                taxEl.innerText = `Tax: 10% (Php ${tax})`;
                totalEl.innerText = `Total: Php ${total}`;

                document.querySelectorAll('.quantity-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        const action = btn.dataset.action;
                        if (action === 'add') cart[id].quantity++;
                        if (action === 'subtract' && cart[id].quantity > 1) cart[id].quantity--;
                        renderCart();
                    });
                });

                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        delete cart[id];
                        renderCart();
                    });
                });
            }

            function selectCategory(tile, categoryId) {
                selectedCategoryId = categoryId;
                categoryTiles.style.display = 'none';
                backBtn.style.display = 'block';
                productGrid.style.display = 'grid';

                fetch(`${baseUrl}/pos/category/${categoryId}/products`)
                    .then(res => res.json())
                    .then(products => {
                        currentProducts = products;
                        renderProducts(products);
                    })
                    .catch(err => console.error(err));
            }

            function renderProducts(products) {
                productGrid.innerHTML = '';
                products.forEach(p => {
                    const div = document.createElement('div');
                    div.className = "bg-white border border-gray-300 rounded-lg p-4 flex flex-col items-center justify-center cursor-pointer hover:bg-blue-50 transition";

                    const imageUrl = p.image ? `${baseUrl}/storage/${p.image}` : '';
                    div.innerHTML = `
                        <div class="bg-gray-200 w-full h-24 mb-2 flex items-center justify-center rounded overflow-hidden">
                            ${imageUrl ? `<img src="${imageUrl}" class="w-full h-full object-contain"/>` : `<span class="text-gray-400 text-sm">No Image</span>`}
                        </div>
                        <p class="font-medium">${p.name}</p>
                        <p class="text-sm">Php ${p.sell_price}</p>
                        <p class="text-xs text-gray-500">Stock: ${p.stock}</p>
                    `;

                    div.addEventListener('click', () => {
                        if (cart[p.id]) cart[p.id].quantity++;
                        else cart[p.id] = {...p, quantity:1};
                        renderCart();
                    });

                    productGrid.appendChild(div);
                });
            }

            categoryTiles.querySelectorAll('.category-tile').forEach(tile => {
                tile.addEventListener('click', () => selectCategory(tile, tile.dataset.id));
            });

            backBtn.addEventListener('click', function() {
                selectedCategoryId = null;
                productGrid.style.display = 'none';
                categoryTiles.style.display = 'grid';
                backBtn.style.display = 'none';
                searchBar.value = '';
            });

            searchBar.addEventListener('input', function() {
                const query = this.value.toLowerCase();

                if (selectedCategoryId) {
                    const filtered = currentProducts.filter(p => p.name.toLowerCase().includes(query));
                    renderProducts(filtered);
                } else {
                    categoryTiles.querySelectorAll('.category-tile').forEach(tile => {
                        tile.style.display = tile.innerText.toLowerCase().includes(query) ? 'flex' : 'none';
                    });
                }
            });

            completeBtn.addEventListener('click', () => {
            if (Object.keys(cart).length === 0) return alert("Cart is empty!");

            const cartArray = Object.values(cart).map(item => ({
                id: item.id,
                name: item.name,
                sell_price: item.sell_price,
                stock: item.stock,
                quantity: item.quantity
            }));

            const payload = {
                cart: cartArray,
                payment_method: paymentMethod.value
            };

            if (paymentMethod.value === 'cash') {
                fetch(`${baseUrl}/pos/complete-order`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert('Order completed successfully!');

                        // Open receipt in new tab
                        if (data.receiptUrl) {
                            window.open(data.receiptUrl, '_blank');
                        }

                        // Clear cart
                        Object.keys(cart).forEach(k => delete cart[k]);
                        renderCart();

                        productGrid.style.display = 'none';
                        categoryTiles.style.display = 'grid';
                        backBtn.style.display = 'none';
                        searchBar.value = '';
                    } else {
                        alert('Error completing order: ' + (data.error || 'Unknown'));
                    }
                })
                .catch(err => alert("Error: " + err.message));

                return;
            }

            fetch(`${baseUrl}/pos/paymongo/create-checkout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (!data.checkout_url) {
                    alert("Error creating PayMongo checkout!");
                    return;
                }

                window.open(data.checkout_url, "_blank");

                pollPayment(data.checkout_id);
            });
        });

        function pollPayment(checkoutId) {
            let interval = setInterval(() => {
                fetch(`${baseUrl}/pos/paymongo/check-status/${checkoutId}`)
                    .then(res => res.json())
                    .then(data => {
                        console.log("Polling status:", data.status);

                        if (data.status === 'succeeded') {
                            clearInterval(interval);

                            fetch(`${baseUrl}/pos/paymongo/finalize/${checkoutId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                }
                            })
                            .then(res => res.json())
                            .then(finalizeData => {
                                alert(finalizeData.message || "Order finalized!");
                                
                                Object.keys(cart).forEach(k => delete cart[k]);
                                renderCart();
                                productGrid.style.display = 'none';
                                categoryTiles.style.display = 'grid';
                                backBtn.style.display = 'none';
                                searchBar.value = '';
                            })
                            .catch(err => console.error("Finalize error:", err));
                        }

                        if (data.status === 'failed' || data.status === 'expired') {
                            clearInterval(interval);
                            alert("Payment failed or expired");
                        }
                    })
                    .catch(err => {
                        clearInterval(interval);
                        console.error("Polling error", err);
                    });

            }, 3000);
        }

    });
    </script>

</body>
</html>
