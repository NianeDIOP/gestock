<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AYIB DIOP - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Base */
        body {
            min-height: 100vh;
            background: #f8fafc;
        }

        /* Layout Structure */
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 64px;
            background: white;
            z-index: 50;
            border-bottom: 1px solid #e2e8f0;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 64px;
            left: 0;
            bottom: 0;
            width: 240px;
            background: #2d3748;
            color: white;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 40;
        }

        .sidebar-nav {
            padding: 1rem;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #e2e8f0;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .nav-item a:hover {
            background: #4a5568;
            transform: translateX(4px);
        }

        .nav-item a.active {
            background: #4299e1;
            color: white;
        }

        .nav-item i {
            width: 20px;
            margin-right: 0.75rem;
        }

        /* Main Content */
        .main-content {
            margin-left: 240px;
            margin-top: 64px;
            padding: 1.5rem;
            min-height: calc(100vh - 64px);
            background: white;
            flex: 1;
        }

        .floating-buttons {
            position: fixed;
            bottom: 1rem;
            left: 1rem;
            z-index: 100;
            display: flex;
            gap: 0.75rem;
        }
        .floating-buttons button {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .floating-buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        }
        /* Footer */
        footer {
            margin-left: 240px;
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 60;
            background: white;
            padding: 0.75rem;
            border-radius: 0.375rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Mobile Navigation */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 64px;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            z-index: 45;
        }

        /* Mobile Overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 35;
        }

        /* Responsive Media Queries */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }

            .sidebar.mobile-active {
                transform: translateX(0);
            }

            .mobile-overlay.active {
                display: block;
            }

            .mobile-toggle {
                display: block;
            }

            .mobile-menu.active {
                display: block;
            }

            .main-content,
            footer {
                margin-left: 0;
            }

            .desktop-nav {
                display: none;
            }
        }

        /* Large Screens */
        @media (min-width: 1920px) {
            .sidebar {
                width: 280px;
            }

            .main-content,
            footer {
                margin-left: 280px;
            }
        }

        /* Utils */
        .truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
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

    <div class="wrapper">
        <!-- Mobile Toggle Button -->
        <button class="mobile-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars text-gray-600 text-xl"></i>
        </button>

        <!-- Mobile Overlay -->
        <div class="mobile-overlay" onclick="toggleMobileMenu()"></div>

        <!-- Navbar -->
       <!-- Navbar -->
       <nav class="navbar">
        <div class="w-full h-full px-4">
            <div class="flex items-center justify-between h-full max-w-[2000px] mx-auto">
                <!-- Logo et nom - Extrême gauche -->
                <div class="flex items-center">
                    <i class="fas fa-warehouse text-blue-800 text-xl mr-2"></i>
                    <span class="font-bold text-gray-1000">{{ $settings['name'] }}</span>
                </div>
    
                <!-- Infos droite (toutes visibles en desktop, masquées en mobile) -->
                <div class="hidden md:flex items-center space-x-6">
                    <!-- Téléphone et email -->
                    <span class="flex items-center text-gray-600">
                        <i class="fas fa-phone mr-2"></i>
                        {{ $settings['phone'] }}
                    </span>
                    <span class="flex items-center text-gray-600">
                        <i class="fas fa-envelope mr-2"></i>
                        {{ $settings['email'] }}
                    </span>
    
                    <!-- Adresse et date -->
                    <span class="flex items-center text-gray-600">
                        <i class="fas fa-map-marker-alt mr-2"></i>
                        {{ $settings['address'] }}
                    </span>
                    <span class="flex items-center text-gray-600">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        {{ now()->format('d/m/Y') }}
                    </span>
    
                    <!-- Déconnexion -->
                    <form action="{{ route('auth.logout') }}" method="POST" class="flex">
                        @csrf
                        <button type="submit" class="flex items-center text-gray-600 hover:text-blue-600">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Déconnexion
                        </button>
                    </form>
                </div>
    
                <!-- Bouton de déconnexion (visible uniquement en mobile) -->
                <div class="md:hidden flex items-center">
                    <form action="{{ route('auth.logout') }}" method="POST" class="flex">
                        @csrf
                        <button type="submit" class="flex items-center text-gray-600 hover:text-blue-600">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

        <!-- Mobile Menu -->
        <!-- Mobile Menu -->
        <div class="mobile-menu">
            <div class="space-y-3">
                <!-- Téléphone et email -->
                <p class="text-sm text-gray-600">
                    <i class="fas fa-phone mr-2"></i>{{ $settings['phone'] }}
                </p>
                <p class="text-sm text-gray-600">
                    <i class="fas fa-envelope mr-2"></i>{{ $settings['email'] }}
                </p>

                <!-- Déconnexion -->
                <div class="pt-2 border-t">
                    <form action="{{ route('auth.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 hover:text-blue-600">
                            <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="fas fa-list"></i>
                            <span>Catégories</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="fas fa-box"></i>
                            <span>Matériels</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice"></i>
                            <span>Factures</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                            <i class="fas fa-truck"></i>
                            <span>Fournisseurs</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('quotations.index') }}" class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Devis</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('settings') }}" class="{{ request()->routeIs('settings') ? 'active' : '' }}">
                            <i class="fas fa-cog"></i>
                            <span>Paramètres</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>

          <!-- Boutons flottants -->
        <div class="floating-buttons">
            <button onclick="openSaleModal()" class="bg-blue-600 text-white">
                <i class="fas fa-cash-register"></i>
            </button>
            <button onclick="openStockModal()" class="bg-green-600 text-white">
                <i class="fas fa-boxes"></i>
            </button>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="container mx-auto px-4 py-3">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <!-- Numéro et email (visibles uniquement en mobile) -->
                    <div class="md:hidden flex flex-col items-center space-y-2">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-phone mr-2"></i>{{ $settings['phone'] }}
                        </span>
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-envelope mr-2"></i>{{ $settings['email'] }}
                        </span>
                    </div>
        
                    <!-- Copyright et développeur (visibles en desktop et mobile) -->
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">
                            &copy; {{ date('Y') }} {{ $settings['name'] }}
                        </span>
                        <span class="text-sm text-gray-500 italic">
                            Développé par ni@na-diop
                        </span>
                    </div>
                </div>
            </div>
        </footer>
    </div>


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
            // Mobile Menu Toggle
            function toggleMobileMenu() {
                const sidebar = document.querySelector('.sidebar');
                const overlay = document.querySelector('.mobile-overlay');
                const mobileMenu = document.querySelector('.mobile-menu');
                
                sidebar.classList.toggle('mobile-active');
                overlay.classList.toggle('active');
                mobileMenu.classList.toggle('active');
                
                document.body.style.overflow = sidebar.classList.contains('mobile-active') ? 'hidden' : '';
            }
    
            // Close mobile menu when clicking links
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        toggleMobileMenu();
                    }
                });
            });
    
            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    const sidebar = document.querySelector('.sidebar');
                    const overlay = document.querySelector('.mobile-overlay');
                    const mobileMenu = document.querySelector('.mobile-menu');
                    
                    sidebar.classList.remove('mobile-active');
                    overlay.classList.remove('active');
                    mobileMenu.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        </script>

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

    function openMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// Fermer le menu quand on clique à l'extérieur
document.addEventListener('click', function(e) {
    if (!e.target.closest('#mobileMenu') && !e.target.closest('button[onclick="openMobileMenu()"]')) {
        document.getElementById('mobileMenu').classList.add('hidden');
    }
});

    function toggleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        
        sidebar.classList.toggle('mobile-active');
        overlay.classList.toggle('active');
        
        // Bloquer le scroll quand menu ouvert
        document.body.style.overflow = sidebar.classList.contains('mobile-active') ? 'hidden' : '';
    }

    // Fermer le menu quand on clique sur un lien
    document.querySelectorAll('.sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                toggleMobileMenu();
            }
        });
    });

    // Réinitialiser au redimensionnement
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            document.querySelector('.sidebar').classList.remove('mobile-active');
            document.querySelector('.mobile-overlay').classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    </script>
    @stack('scripts')
</body>
</html>