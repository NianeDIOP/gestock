@extends('layouts.app')

@section('title', 'Liste des produits')

@section('content')
<div class="container px-6 mx-auto">
    <!-- 1. ENTÊTE AVEC BOUTONS -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <!-- Titre -->
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Liste des produits</h2>
            <p class="text-gray-600 mt-1">{{ $products->total() }} produits au total</p>
        </div>

        <!-- Boutons d'action -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Bouton Rupture -->
            <a href="{{ route('products.out_of_stock') }}" 
               class="px-3 py-2 bg-orange-600 text-white text-sm rounded hover:bg-orange-700">
                <i class="fas fa-exclamation-triangle mr-2"></i>Ruptures
            </a>

            <!-- Bouton Nouveau produit -->
            <button onclick="openAddModal()" 
                    class="px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Nouveau
            </button>

            <!-- Bouton Export PDF -->
            <a href="{{ route('products.export.pdf', request()->query()) }}" 
               class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">
                <i class="fas fa-file-pdf mr-2"></i>Export PDF
            </a>

            <!-- Formulaire Rapport - Maintenant dans un form -->
            <form action="{{ route('products.generate-report') }}" method="GET" class="flex items-center gap-2">
                <select name="period" class="px-3 py-2 text-sm border rounded bg-white">
                    <option value="day">Jour</option>
                    <option value="week">Semaine</option>
                    <option value="month" selected>Mois</option>
                    <option value="year">Année</option>
                </select>
                
                <select name="year" class="px-3 py-2 text-sm border rounded bg-white">
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <button type="submit" 
                        class="px-3 py-2 bg-purple-600 text-white text-sm rounded hover:bg-purple-700">
                    <i class="fas fa-chart-pie mr-2"></i>Rapport
                </button>
            </form>
        </div>
    </div>

    <!-- 2. FILTRES DE RECHERCHE -->
    <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
        <form action="{{ route('products.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Filtre Catégorie -->
            <select name="category" class="w-full p-2 text-sm border rounded">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" 
                            {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <!-- Champ de recherche -->
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Rechercher par référence ou nom..." 
                   class="w-full p-2 text-sm border rounded">

            <!-- Filtre Statut Stock -->
            <select name="stock_status" class="w-full p-2 text-sm border rounded">
                <option value="">Tous les stocks</option>
                <option value="rupture" {{ request('stock_status') == 'rupture' ? 'selected' : '' }}>
                    En rupture
                </option>
                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>
                    Stock faible
                </option>
                <option value="sufficient" {{ request('stock_status') == 'sufficient' ? 'selected' : '' }}>
                    Stock suffisant
                </option>
            </select>

            <!-- Boutons de recherche -->
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                </button>
                @if(request()->anyFilled(['search', 'category', 'stock_status']))
                    <a href="{{ route('products.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tableau des produits -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Référence</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Catégorie</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Quantité</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Seuil de stock</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->reference }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                @if($product->description)
                                    <div class="text-xs text-gray-500">{{ Str::limit($product->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $product->category->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ number_format($product->price, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-sm">
                                @if($product->quantity == 0)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Rupture
                                    </span>
                                @elseif($product->quantity <= $product->stock_threshold)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Faible ({{ $product->quantity }})
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        OK ({{ $product->quantity }})
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $product->stock_threshold }}</td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                <button onclick="openEditModal({{ $product->id }})" 
                                    class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
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
            <!-- Overlay sombre -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <!-- Contenu du modal -->
            <div class="relative bg-white rounded-lg w-full max-w-2xl">
                <!-- En-tête du modal -->
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Nouveau produit</h3>
                    <button type="button" onclick="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Formulaire -->
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="p-6">
                        <!-- Nom du produit -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required 
                                placeholder="Nom du produit"
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="2" 
                                placeholder="Description du produit"
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Catégorie -->
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" id="category_id" required 
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Sélectionner une catégorie</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Prix -->
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                    Prix <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="price" id="price" required 
                                        min="0" step="0.01" placeholder="0.00"
                                        class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-16">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantité -->
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">
                                    Quantité <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="quantity" id="quantity" required 
                                    min="0" placeholder="0"
                                    class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Seuil de stock -->
                        <div class="mb-4">
                            <label for="stock_threshold" class="block text-sm font-medium text-gray-700 mb-1">
                                Seuil de stock <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="stock_threshold" id="stock_threshold" required 
                                min="0" placeholder="0"
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Pied du modal avec boutons -->
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                        <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
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
            <!-- Overlay sombre -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

            <!-- Contenu du modal -->
            <div class="relative bg-white rounded-lg w-full max-w-2xl">
                <!-- En-tête du modal -->
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Modifier le produit</h3>
                    <button type="button" onclick="closeEditModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Formulaire d'édition -->
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6">
                        <!-- Référence (en lecture seule) -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                            <input type="text" id="edit_reference" readonly 
                                class="w-full px-3 py-2 border rounded-lg bg-gray-50">
                        </div>

                        <!-- Nom -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="edit_name" required 
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" id="edit_description" rows="2" 
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <!-- Catégorie -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <select name="category_id" id="edit_category_id" required 
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <!-- Prix -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Prix <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="price" id="edit_price" required 
                                        min="0" step="0.01"
                                        class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-16">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500">FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Quantité -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Quantité <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="quantity" id="edit_quantity" required 
                                    min="0"
                                    class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <!-- Seuil de stock -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Seuil de stock <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="stock_threshold" id="edit_stock_threshold" required 
                                min="0"
                                class="w-full px-3 py-2 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Pied du modal -->
                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 border rounded-lg text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
    // Fonctions pour le modal d'ajout
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.querySelector('#addModal form').reset();
    }

    // Fonctions pour le modal d'édition
    function openEditModal(id) {
        fetch(`/products/${id}/edit`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de la récupération des données du produit');
                }
                return response.json();
            })
            .then(product => {
                // Mise à jour de l'action du formulaire
                const form = document.getElementById('editForm');
                form.action = `/products/${id}`;

                // Remplissage des champs
                document.getElementById('edit_reference').value = product.reference;
                document.getElementById('edit_name').value = product.name;
                document.getElementById('edit_description').value = product.description || '';
                document.getElementById('edit_category_id').value = product.category_id;
                document.getElementById('edit_price').value = product.price;
                document.getElementById('edit_quantity').value = product.quantity;
                document.getElementById('edit_stock_threshold').value = product.stock_threshold;

                // Affichage du modal
                document.getElementById('editModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des données du produit');
            });
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        document.getElementById('editForm').reset();
    }

    // Fermer les modaux avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddModal();
            closeEditModal();
        }
    });

    // Fermer les modaux en cliquant sur l'overlay
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
            closeAddModal();
            closeEditModal();
        }
    });
    </script>
</div>
@endsection