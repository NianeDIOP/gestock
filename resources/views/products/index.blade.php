@extends('layouts.app')
@section('title', 'Liste des produits')
@section('content')
<div class="container px-6 mx-auto">
    <!-- Notification Toast -->
    <div id="notification" class="fixed top-4 right-4 z-50 hidden transform transition-all duration-300">
        <div id="notification-content" class="rounded-lg shadow-lg">
            <div class="flex items-center">
                <span id="notification-message"></span>
            </div>
        </div>
    </div>

    <!-- En-tête -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Liste des produits</h2>
                <nav class="mt-2">
                    <ol class="flex items-center space-x-2 text-sm text-gray-500">
                        <li><a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Tableau de bord</a></li>
                        <li><i class="fas fa-chevron-right text-xs"></i></li>
                        <li class="text-gray-700">Produits</li>
                    </ol>
                </nav>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('products.out_of_stock') }}" 
                   class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span>Ruptures de stock</span>
                    <span class="ml-2 bg-orange-700 px-2 py-1 rounded-full text-xs">
                        {{ \App\Models\Product::where('quantity', 0)->count() }}
                    </span>
                </a>
                <button onclick="openAddModal()" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    <span>Nouveau produit</span>
                </button>
                <a href="{{ route('products.export.pdf') }}" 
                   class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i>
                    <span>Exporter PDF</span>
                </a>
                <button onclick="openReportModal()" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center">
                    <i class="fas fa-chart-bar mr-2"></i>
                    <span>Générer Rapport</span>
                </button>
            </div>
        </div>
    </div>
    <!-- Filtres améliorés -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Recherche -->
            <div class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Rechercher un produit..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            
            <!-- Filtre par catégorie -->
            <select name="category" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <!-- Filtre par statut de stock -->
            <select name="stock_status" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                <option value="">Tous les stocks</option>
                <option value="rupture" {{ request('stock_status') == 'rupture' ? 'selected' : '' }}>En rupture</option>
                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stock faible</option>
                <option value="sufficient" {{ request('stock_status') == 'sufficient' ? 'selected' : '' }}>Stock suffisant</option>
            </select>

            <!-- Boutons de filtrage -->
            <div class="flex gap-2">
                <button type="submit" 
                        class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i>
                    Filtrer
                </button>
                @if(request()->anyFilled(['search', 'category', 'stock_status']))
                    <a href="{{ route('products.index') }}" 
                       class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i>
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table des produits -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Référence
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Produit
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Catégorie
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Prix
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Stock
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($products as $product)
                    <tr data-product-id="{{ $product->id }}" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $product->reference }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-sm text-gray-500">
                                        {{ Str::limit($product->description, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->category->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                {{ number_format($product->price, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($product->quantity == 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-times-circle mr-1"></i> Rupture
                                    </span>
                                @elseif($product->quantity <= $product->stock_threshold)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        Faible ({{ $product->quantity }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        En stock ({{ $product->quantity }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-3">
                                    <button onclick="openEditModal({{ $product->id }})" 
                                            class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteProduct({{ $product->id }})" 
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 font-medium">
                                Aucun produit trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $products->links() }}
            </div>
        @endif
    </div>
   <!-- Modal d'ajout -->
<div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Contenu du modal -->
        <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>
                        Nouveau produit
                    </h3>
                    <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="addProductForm" action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nom du produit <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Catégorie -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Sélectionner une catégorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Prix -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Prix <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="price" 
                                       required
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 pr-16">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">FCFA</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stock initial -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Stock initial <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="quantity" 
                                   required
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Seuil d'alerte -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Seuil d'alerte <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="stock_threshold" 
                                   required
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <!-- Description -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                     rows="3"
                                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-xl">
                    <button type="button" 
                            onclick="closeAddModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Modal d'édition -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Contenu du modal -->
        <div class="relative bg-white rounded-xl shadow-2xl max-w-2xl w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-edit mr-2 text-indigo-600"></i>
                        Modifier le produit
                    </h3>
                    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Référence (en lecture seule) -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Référence
                            </label>
                            <input type="text" 
                                   id="edit_reference" 
                                   readonly
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-500">
                        </div>

                        <!-- Nom -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="edit_name" 
                                   required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Catégorie -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" 
                                    id="edit_category_id" 
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Prix -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Prix <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="price" 
                                       id="edit_price" 
                                       required
                                       min="0"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 pr-16">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">FCFA</span>
                                </div>
                            </div>
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Stock actuel <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="quantity" 
                                   id="edit_quantity" 
                                   required
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Seuil d'alerte -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Seuil d'alerte <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="stock_threshold" 
                                   id="edit_stock_threshold" 
                                   required
                                   min="0"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <div class="error-message text-red-500 text-sm mt-1 hidden"></div>
                        </div>

                        <!-- Description -->
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                     id="edit_description" 
                                     rows="3"
                                     class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-xl">
                    <button type="button" 
                            onclick="closeEditModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de génération de rapport -->
<div id="reportModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Contenu du modal -->
        <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">
                        <i class="fas fa-chart-bar mr-2 text-purple-600"></i>
                        Générer un rapport
                    </h3>
                    <button type="button" onclick="closeReportModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('products.generate.report') }}" method="GET">
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Sélection de la période -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Période <span class="text-red-500">*</span>
                            </label>
                            <select name="period" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                                <option value="day">Jour</option>
                                <option value="week">Semaine</option>
                                <option value="month">Mois</option>
                                <option value="year">Année</option>
                            </select>
                        </div>

                        <!-- Sélection de l'année -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Année <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="year" 
                                   value="{{ date('Y') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"
                                   placeholder="Année">
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-xl">
                    <button type="button" 
                            onclick="closeReportModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-times mr-2"></i>
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors flex items-center">
                        <i class="fas fa-check mr-2"></i>
                        Générer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    @push('scripts')
    @push('scripts')
    <script>
    // Système de notification amélioré
    const notificationSystem = {
        show(message, type = 'success') {
            const notification = document.getElementById('notification');
            const content = notification.querySelector('#notification-content');
            const messageEl = notification.querySelector('#notification-message');
    
            // Configuration selon le type
            const configs = {
                success: {
                    bgColor: 'bg-green-500',
                    icon: 'fa-check-circle'
                },
                error: {
                    bgColor: 'bg-red-500',
                    icon: 'fa-exclamation-circle'
                },
                warning: {
                    bgColor: 'bg-yellow-500',
                    icon: 'fa-exclamation-triangle'
                }
            };
    
            const config = configs[type];
            content.className = `${config.bgColor} text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300`;
            messageEl.innerHTML = `<i class="fas ${config.icon} mr-2"></i>${message}`;
    
            // Animation d'entrée
            notification.classList.remove('hidden');
            notification.classList.add('translate-y-0', 'opacity-100');
            
            // Animation de sortie
            setTimeout(() => {
                notification.classList.add('-translate-y-full', 'opacity-0');
                setTimeout(() => {
                    notification.classList.add('hidden');
                    notification.classList.remove('-translate-y-full', 'opacity-0');
                }, 300);
            }, 3000);
        }
    };
    
    // Modal d'ajout
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        resetForm('addProductForm');
    }
    
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        resetForm('addProductForm');
    }
    
    // Modal d'édition
    async function openEditModal(id) {
        try {
            const response = await fetch(`/products/${id}/edit`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
    
            if (!response.ok) throw new Error('Erreur de chargement');
            
            const product = await response.json();
            
            // Remplir le formulaire
            document.getElementById('editForm').action = `/products/${id}`;
            document.getElementById('edit_reference').value = product.reference;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_category_id').value = product.category_id;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_quantity').value = product.quantity;
            document.getElementById('edit_stock_threshold').value = product.stock_threshold;
            document.getElementById('edit_description').value = product.description || '';
    
            // Afficher le modal
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
    
        } catch (error) {
            notificationSystem.show('Erreur lors du chargement des données', 'error');
        }
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        resetForm('editForm');
    }
    
    // Suppression
    async function deleteProduct(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
        try {
            const response = await fetch(`/products/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error('Erreur lors de la suppression');
            }

            // Animation et suppression de la ligne
            const row = document.querySelector(`tr[data-product-id="${id}"]`);
            if (row) {
                row.style.transition = 'all 0.3s ease';
                row.style.opacity = '0';
                row.style.transform = 'translateX(100%)';
                
                setTimeout(() => {
                    row.remove();
                    notificationSystem.show('Produit supprimé avec succès', 'success');
                }, 300);
            } else {
                location.reload();
            }

        } catch (error) {
            console.error('Erreur:', error);
            notificationSystem.show('Erreur lors de la suppression', 'error');
        }
    }
}
    
    // Gestion des formulaires
    document.addEventListener('DOMContentLoaded', function() {
        // Formulaire d'ajout
        const addForm = document.getElementById('addProductForm');
        if (addForm) {
            addForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: new FormData(this),
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
    
                    if (!response.ok) throw new Error('Erreur lors de l\'ajout');
    
                    notificationSystem.show('Produit ajouté avec succès', 'success');
                    closeAddModal();
                    window.location.reload();
    
                } catch (error) {
                    notificationSystem.show('Erreur lors de l\'ajout', 'error');
                }
            });
        }
    
        // Formulaire d'édition
        const editForm = document.getElementById('editForm');
        if (editForm) {
            editForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                try {
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT');
    
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
    
                    if (!response.ok) throw new Error('Erreur lors de la mise à jour');
    
                    notificationSystem.show('Produit mis à jour avec succès', 'success');
                    closeEditModal();
                    window.location.reload();
    
                } catch (error) {
                    notificationSystem.show('Erreur lors de la mise à jour', 'error');
                }
            });
        }
    });
    
    // Réinitialisation des formulaires
    function resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();
            form.querySelectorAll('.error-message').forEach(el => {
                el.textContent = '';
                el.classList.add('hidden');
            });
        }
    }
    
    // Gestionnaires d'événements globaux
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });

    // Fonctions pour la modale de rapport
function openReportModal() {
    document.getElementById('reportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Gestionnaire d'événements pour la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
        closeReportModal(); // Ajouter cette ligne
    }
});
    </script>
    @endpush
@endpush

</div>
@endsection