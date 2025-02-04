<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AYIB DIOP - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Sidebar styles */
        .sidebar {
            background-color: #2d3748;
            color:rgb(3, 47, 92);
            font-size: 0.9rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 100vh;
        }
        .sidebar:hover {
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }
        .nav-item {
            margin-bottom: 1rem;
        }
        .nav-item a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            color: rgb(231, 231, 231);
            text-decoration: none;
        }
        .nav-item a:hover {
            background-color:rgb(39, 95, 192);
            transform: translateX(5px);
        }
        .active {
            background-color:rgb(10, 110, 79);
            border-left: 4px solidrgb(1, 33, 59);
        }
        .navbar {
            background: linear-gradient(135deg, rgb(220, 224, 235), #2563eb);
            padding: 1rem 1.5rem;
        }
        .footer-sidebar {
            background-color: #1a202c;
            padding: 1rem;
            text-align: center;
            margin-top: auto;
        }

        footer {
        background: linear-gradient(135deg, rgb(220, 224, 235), #2563eb);
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    }

    footer {
        background: white;
        border-top: 1px solid #e2e8f0;
    }

    footer i {
        font-size: 0.8rem;
    }
    </style>
</head>
<body class="bg-gray-100">
    @php
        $settings = Cache::get('settings', [
            'name' => 'Nom de la quincaillerie',
            'address' => 'Adresse non définie',
            'phone' => 'Numéro non défini',
            'ninea' => 'NINEA non défini',
        ]);
    @endphp
    @php
        $settings = App\Models\Setting::first();
    @endphp
    @php
        $products = App\Models\Product::select('id', 'name', 'reference', 'quantity', 'price')->get();
    @endphp

    <!-- Navbar -->
    <nav class="bg-white shadow-sm py-2">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-lg font-bold flex items-center text-gray-800">
                <i class="fas fa-warehouse mr-2 text-blue-600"></i> {{ $settings['name'] }}
            </h1>

            <!-- Dans la section navbar -->
            <div class="flex items-center gap-4">
                <button onclick="openSaleModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                    <i class="fas fa-cash-register mr-2"></i>Vente Produit
                </button>
                <button onclick="openStockModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    <i class="fas fa-boxes mr-2"></i>Approvisionnement
                </button>
            </div>

            <div class="flex items-center space-x-4">
                <p class="text-sm text-gray-800 flex items-center">
                    <i class="fas fa-map-marker-alt mr-2 text-gray-600"></i> {{ $settings['address'] }}
                </p>
                <p class="text-sm text-gray-800 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-gray-600"></i> {{ now()->format('d/m/Y') }}
                </p>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center text-sm text-gray-800 hover:text-blue-600">
                        <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <div class="flex">
        <!-- Sidebar -->
        <div class="sidebar w-60">
            <nav class="p-4">
                <ul>
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="hover:bg-gray-600 rounded-lg transition duration-200 {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt mr-6"></i> Tableau de bord
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('categories.index') }}" class="hover:bg-gray-600 rounded-lg transition duration-200 {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="fas fa-list mr-6"></i> Catégories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="hover:bg-gray-600 rounded-lg transition duration-200 {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="fas fa-box mr-6"></i> Matériels
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('sales.index') }}" class="hover:bg-gray-600 rounded-lg transition duration-200 {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice mr-6"></i> Factures
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('suppliers.index') }}" class="hover:bg-gray-600 rounded-lg transition duration-200 {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-truck mr-6"></i> Fournisseurs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('quotations.index') }}" class="hover:bg-gray-600 rounded-lg transition duration-200 {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar mr-6"></i> Devis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('settings') }}" class="hover:bg-gray-600 rounded-lg transition duration-200 {{ request()->routeIs('settings') ? 'active' : '' }}">
                            <i class="fas fa-cog mr-6"></i> Paramètres
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Contenu dynamique -->
        <div class="flex-1 p-6 bg-white shadow-inner">
            @yield('content')
        </div>
    </div>

    <!-- Nouveau pied de page -->
    <footer class="bg-white shadow-sm py-3 border-t"> <!-- Réduction du padding -->
    <div class="container mx-auto flex justify-between items-center px-6">
        <!-- Colonne de gauche - Informations société -->
        <div class="flex items-center space-x-4"> <!-- Espacement réduit -->
            <p class="text-xs text-gray-700"> <!-- Taille réduite -->
                <i class="fas fa-id-card mr-1 text-gray-600"></i> NINEA : {{ $settings['ninea'] }}
            </p>
            <p class="text-xs text-gray-700">
                <i class="fas fa-phone mr-1 text-gray-600"></i> {{ $settings['phone'] }}
            </p>
        </div>

        <!-- Colonne de droite - Copyright et crédits -->
        <div class="flex items-center space-x-3">
            <p class="text-xs text-gray-600">
                &copy; {{ date('Y') }} {{ $settings['name'] }}
            </p>
            <p class="text-xs text-gray-500 italic"> <!-- Couleur plus claire -->
                Développé par ni@na-diop
            </p>
        </div>
    </div>
</footer>

    <!-- Modal Approvisionnement -->
    <div id="stockModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div class="relative bg-white rounded-lg w-full max-w-2xl">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-medium">Approvisionnement Stock</h3>
                    <button onclick="closeStockModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <input type="text" 
                            id="productSearch" 
                            placeholder="Rechercher un produit..." 
                            class="w-full px-4 py-2 border rounded-lg"
                            oninput="searchStockProducts(this.value)">
                    </div>

                    <div id="searchResults" class="max-h-60 overflow-y-auto mb-4 hidden">
                        <!-- Résultats de recherche -->
                    </div>

                    <form id="stockForm" onsubmit="submitStock(event)" class="hidden">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Produit</label>
                                <input type="text" id="selectedProduct" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                                <input type="hidden" id="productId">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité actuelle</label>
                                <input type="text" id="currentStock" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantité à ajouter</label>
                            <input type="number" id="addQuantity" required min="1" class="w-full px-4 py-2 border rounded-lg">
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeStockModal()" class="px-4 py-2 border rounded-lg">
                                Annuler
                            </button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nouvelle Vente -->
    <div id="saleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <div class="relative bg-white rounded-lg w-full max-w-4xl">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-gray-800">Nouvelle Vente</h3>
                    <button type="button" onclick="closeSaleModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="saleForm" onsubmit="submitSale(event)">
                    <div class="p-6 space-y-6">
                        <!-- Client -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du client</label>
                                <input type="text" name="client_name" 
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                                <input type="text" name="client_phone"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                            </div>
                        </div>

                        <!-- Produits -->
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-medium text-gray-700">Produits <span class="text-red-500">*</span></h4>
                                <button type="button" onclick="addProductRow()" 
                                    class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 flex items-center">
                                    <i class="fas fa-plus mr-2"></i>Ajouter
                                </button>
                            </div>

                            <div id="productsContainer" class="space-y-4 max-h-[300px] overflow-y-auto pr-2">
                                <!-- Les produits seront ajoutés ici -->
                            </div>

                            <div id="noProducts" class="text-center text-gray-500 py-4">
                                Aucun produit ajouté
                            </div>
                        </div>

                        <!-- Totaux -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Sous-total:</span>
                                    <span id="subtotal" class="font-medium">0 FCFA</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">TVA (%):</span>
                                    <input type="number" name="tax_rate" min="0" max="100" value="0" 
                                        class="w-24 px-3 py-2 border-2 border-gray-300 rounded text-right"
                                        onchange="calculateTotals()" onkeyup="calculateTotals()">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">TVA:</span>
                                    <span id="tax" class="font-medium">0 FCFA</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t">
                                    <span class="font-bold">Total:</span>
                                    <span id="total" class="font-bold text-xl">0 FCFA</span>
                                </div>
                            </div>
                        </div>

                        <!-- Paiement et Notes -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mode de paiement <span class="text-red-500">*</span></label>
                                <select name="payment_method" required 
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500">
                                    <option value="cash">Espèces</option>
                                    <option value="card">Wave</option>
                                    <option value="other">Orange Money</option>
                                    <option value="other">Free Money</option>
                                    <option value="other">Carte Bancaire</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea name="notes" rows="1"
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-lg">
                        <button type="button" onclick="closeSaleModal()" 
                            class="px-6 py-3 border-2 rounded-lg text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" id="submitSaleBtn" disabled
                            class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-check mr-2"></i>Terminer la vente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Template pour la ligne de produit -->
    <template id="productRowTemplate">
        <div class="product-row bg-gray-50 p-3 rounded-lg">
            <div class="grid grid-cols-12 gap-3 items-center">
                <div class="col-span-5">
                    <input type="text" placeholder="Rechercher un produit..."
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500"
                        oninput="searchProducts(this)">
                    <div class="product-suggestions hidden mt-1 absolute z-10 w-80 bg-white border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                        <!-- Les suggestions seront ajoutées ici -->
                    </div>
                </div>
                <div class="col-span-2">
                    <input type="number" name="quantity" min="1" value="1" required
                        class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500 text-right"
                        onchange="updateRowTotal(this)" onkeyup="updateRowTotal(this)">
                </div>
                <div class="col-span-3">
                    <span class="row-total block text-right font-medium">0 FCFA</span>
                    <span class="unit-price block text-right text-sm text-gray-500"></span>
                </div>
                <div class="col-span-2 flex justify-end">
                    <button type="button" onclick="removeProductRow(this)"
                        class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        </template>

    <script>
        let stockProducts = @json($products);

        function openStockModal() {
            document.getElementById('stockModal').classList.remove('hidden');
            document.getElementById('productSearch').value = '';
            document.getElementById('searchResults').innerHTML = '';
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('stockForm').classList.add('hidden');
        }

        function closeStockModal() {
            document.getElementById('stockModal').classList.add('hidden');
        }

        function searchStockProducts(query) {
            const resultsContainer = document.getElementById('searchResults');
            query = query.toLowerCase();

            const filteredProducts = stockProducts.filter(product =>
                product.name.toLowerCase().includes(query) || 
                product.reference.toLowerCase().includes(query)
            );

            if (filteredProducts.length === 0) {
                resultsContainer.innerHTML = `<div class="p-3 text-gray-500">Aucun produit trouvé.</div>`;
                resultsContainer.classList.remove('hidden');
                return;
            }

            resultsContainer.innerHTML = filteredProducts.map(product => `
                <div class="p-3 hover:bg-gray-50 cursor-pointer border-b" 
                     onclick="selectStockProduct(${product.id}, '${product.name}', ${product.quantity})">
                    <div class="font-medium">${product.name}</div>
                    <div class="text-sm text-gray-500">Réf: ${product.reference} - Stock: ${product.quantity}</div>
                </div>
            `).join('');

            resultsContainer.classList.remove('hidden');
        }

        function selectStockProduct(id, name, quantity) {
            document.getElementById('productId').value = id;
            document.getElementById('selectedProduct').value = name;
            document.getElementById('currentStock').value = quantity;
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('stockForm').classList.remove('hidden');
        }

        async function submitStock(event) {
            event.preventDefault();
            const productId = document.getElementById('productId').value;
            const quantity = document.getElementById('addQuantity').value;

            try {
                const response = await fetch(`/products/${productId}/stock`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ quantity: parseInt(quantity) })
                });

                if (response.ok) {
                    alert('Stock mis à jour avec succès');
                    closeStockModal();
                    location.reload();
                } else {
                    throw new Error('Erreur lors de la mise à jour');
                }
            } catch (error) {
                alert('Erreur: ' + error.message);
            }
        }

        // Fonctions globales
        function refreshStats() {
            const year = document.getElementById('yearFilter').value;
            const period = document.getElementById('periodFilter').value;
            window.location.href = `/dashboard?year=${year}&period=${period}`;
        }

        // ==============================================
    // GESTION DES VENTES
    // ==============================================
    let saleProducts = @json($products);
    let selectedProducts = new Set();

    function openSaleModal() {
        document.getElementById('saleModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        addProductRow();
    }

    function closeSaleModal() {
        document.getElementById('saleModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('saleForm').reset();
        document.getElementById('productsContainer').innerHTML = '';
        selectedProducts.clear();
        document.getElementById('noProducts').style.display = 'block';
        document.getElementById('submitSaleBtn').disabled = true;
    }

    function addProductRow() {
        const template = document.getElementById('productRowTemplate');
        const container = document.getElementById('productsContainer');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
        
        document.getElementById('noProducts').style.display = 'none';
        updateSubmitButton();
    }

    function searchProducts(input) {
        const searchTerm = input.value.toLowerCase();
        const row = input.closest('.product-row');
        const suggestionsDiv = row.querySelector('.product-suggestions');
        
        if (searchTerm.length < 2) {
            suggestionsDiv.classList.add('hidden');
            return;
        }

        const availableProducts = saleProducts.filter(p => 
            !selectedProducts.has(p.id) && 
            (p.name.toLowerCase().includes(searchTerm) || 
             p.reference.toLowerCase().includes(searchTerm))
        );

        suggestionsDiv.innerHTML = availableProducts.map(p => `
            <div class="suggestion p-2 hover:bg-gray-100 cursor-pointer" 
                onclick="selectProduct(this, ${p.id}, '${p.name}', ${p.price}, ${p.quantity})">
                <div class="font-medium">${p.name}</div>
                <div class="text-sm text-gray-500">
                    Ref: ${p.reference} - Stock: ${p.quantity}
                </div>
            </div>
        `).join('');

        suggestionsDiv.classList.remove('hidden');
    }

    function selectProduct(element, id, name, price, stock) {
        const row = element.closest('.product-row');
        const input = row.querySelector('input[type="text"]');
        const quantityInput = row.querySelector('input[type="number"]');
        const unitPrice = row.querySelector('.unit-price');
        
        input.value = name;
        input.dataset.productId = id;
        input.dataset.price = price;
        input.dataset.stock = stock;

        quantityInput.max = stock;
        unitPrice.textContent = `${price.toLocaleString('fr-FR')} FCFA/unité`;
        row.querySelector('.product-suggestions').classList.add('hidden');
        selectedProducts.add(id);
        updateRowTotal(quantityInput);
        updateSubmitButton();
    }

    function updateRowTotal(input) {
        const row = input.closest('.product-row');
        const productInput = row.querySelector('input[type="text"]');
        const quantity = parseInt(input.value);
        const price = parseFloat(productInput.dataset.price);
        const stock = parseInt(productInput.dataset.stock);
        
        if (quantity > stock) {
            input.value = stock;
            alert('Quantité supérieure au stock disponible!');
            return;
        }
        
        const total = price * quantity;
        row.querySelector('.row-total').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
        calculateTotals();
    }

    function removeProductRow(button) {
        const row = button.closest('.product-row');
        const productId = row.querySelector('input[type="text"]').dataset.productId;
        if (productId) selectedProducts.delete(parseInt(productId));
        
        row.remove();
        if (document.querySelectorAll('.product-row').length === 0) {
            document.getElementById('noProducts').style.display = 'block';
        }
        calculateTotals();
        updateSubmitButton();
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.product-row').forEach(row => {
            const productInput = row.querySelector('input[type="text"]');
            if (productInput.dataset.productId) {
                const price = parseFloat(productInput.dataset.price);
                const quantity = parseInt(row.querySelector('input[type="number"]').value);
                subtotal += price * quantity;
            }
        });
        
        const taxRate = parseFloat(document.querySelector('[name="tax_rate"]').value) / 100;
        const tax = subtotal * taxRate;
        const total = subtotal + tax;
        
        document.getElementById('subtotal').textContent = `${subtotal.toLocaleString('fr-FR')} FCFA`;
        document.getElementById('tax').textContent = `${tax.toLocaleString('fr-FR')} FCFA`;
        document.getElementById('total').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
    }

    function updateSubmitButton() {
        const hasProducts = document.querySelectorAll('.product-row input[data-product-id]').length > 0;
        document.getElementById('submitSaleBtn').disabled = !hasProducts;
    }

    async function submitSale(event) {
        event.preventDefault();
        const submitBtn = document.getElementById('submitSaleBtn');
        submitBtn.disabled = true;
        
        const formData = new FormData(event.target);
        const items = [];
        
        document.querySelectorAll('.product-row').forEach(row => {
            const productInput = row.querySelector('input[type="text"]');
            const productId = productInput.dataset.productId;
            if (productId) {
                items.push({
                    product_id: productId,
                    quantity: row.querySelector('input[type="number"]').value
                });
            }
        });
        
        const data = {
            client_name: formData.get('client_name'),
            client_phone: formData.get('client_phone'),
            payment_method: formData.get('payment_method'),
            notes: formData.get('notes'),
            tax_rate: formData.get('tax_rate'),
            items: items
        };
        
        try {
            const response = await fetch('/sales', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();
            
            if (response.ok) {
                closeSaleModal();
                window.open(`/sales/${result.sale_id}/pdf`, '_blank');
                location.reload();
            } else {
                alert(result.error || 'Erreur lors de l\'enregistrement de la vente');
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Erreur lors de l\'enregistrement de la vente');
            submitBtn.disabled = false;
        }
    }

    // Gestionnaire d'événements
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.product-row')) {
                document.querySelectorAll('.product-suggestions').forEach(div => {
                    div.classList.add('hidden');
                });
            }
        });
    });
    </script>
    @stack('scripts')
</body>
</html>