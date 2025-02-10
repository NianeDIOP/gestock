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
    <div class="container px-4 md:px-6 mx-auto">
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex flex-col lg:flex-row justify-between gap-4">
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-gray-800">Liste des produits</h2>
                    <nav class="mt-2">
                        <ol class="flex flex-wrap items-center gap-2 text-sm text-gray-500">
                            <li><a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Tableau de bord</a></li>
                            <li><i class="fas fa-chevron-right text-xs"></i></li>
                            <li class="text-gray-700">Produits</li>
                        </ol>
                    </nav>
                </div>
                <!-- Boutons d'actions responsive -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('products.out_of_stock') }}" 
                        class="inline-flex items-center text-sm bg-orange-600 text-white px-2.5 py-1.5 rounded hover:bg-orange-700 transition-colors">
                        <i class="fas fa-exclamation-triangle text-sm mr-1.5"></i>Ruptures
                        <span class="ml-1.5 bg-orange-700 px-1.5 rounded text-xs">
                            {{ \App\Models\Product::where('quantity', 0)->count() }}
                        </span>
                    </a>
                    
                    <button onclick="openAddModal()" 
                        class="inline-flex items-center text-sm bg-indigo-600 text-white px-2.5 py-1.5 rounded hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus text-sm mr-1.5"></i>Nouveau
                    </button>
                    
                    <a href="{{ route('products.export.pdf') }}" 
                        class="inline-flex items-center text-sm bg-red-600 text-white px-2.5 py-1.5 rounded hover:bg-red-700 transition-colors">
                        <i class="fas fa-file-pdf text-sm mr-1.5"></i>Liste PDF
                    </a>
                    
                    <button onclick="openReportModal()" 
                        class="inline-flex items-center text-sm bg-purple-600 text-white px-2.5 py-1.5 rounded hover:bg-purple-700 transition-colors">
                        <i class="fas fa-chart-bar text-sm mr-1.5"></i>Rapport produit
                    </button>
                </div>
        </div>
    </div>
    <!-- Filtres améliorés -->
    <!-- Filtres responsive -->
        <div class="bg-white p-4 rounded-lg shadow-sm mb-6">
            <form action="{{ route('products.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Rechercher..."
                            class="w-full pl-10 pr-4 py-2 border rounded-lg">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <select name="category" class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Catégories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
        
                    <select name="stock_status" class="w-full px-4 py-2 border rounded-lg">
                        <option value="">Stock</option>
                        <option value="rupture" {{ request('stock_status') == 'rupture' ? 'selected' : '' }}>Rupture</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Faible</option>
                        <option value="sufficient" {{ request('stock_status') == 'sufficient' ? 'selected' : '' }}>Suffisant</option>
                    </select>
        
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-filter mr-2"></i>Filtrer
                        </button>
                        @if(request()->anyFilled(['search', 'category', 'stock_status']))
                            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

    <!-- Table des produits -->
    <!-- Table responsive -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Réf</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produit</th>
                        <th class="hidden md:table-cell px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                        <th class="px-3 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th class="px-3 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 md:px-6 py-2 md:py-4 text-sm font-medium text-gray-900">
                            {{ $product->reference }}
                        </td>
                        <td class="px-3 md:px-6 py-2 md:py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500 truncate max-w-xs">
                                {{ $product->description }}
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-3 md:px-6 py-2 md:py-4 text-sm text-gray-500">
                            {{ $product->category->name }}
                        </td>
                        <td class="px-3 md:px-6 py-2 md:py-4 text-sm whitespace-nowrap">
                            {{ number_format($product->price, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-3 md:px-6 py-2 md:py-4">
                            @if($product->quantity == 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Rupture
                                </span>
                            @elseif($product->quantity <= $product->stock_threshold)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $product->quantity }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    {{ $product->quantity }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 md:px-6 py-2 md:py-4 text-right">
                            <div class="flex justify-end space-x-2">
                                <button onclick="openEditModal({{ $product->id }})" class="p-1">
                                    <i class="fas fa-edit text-indigo-600"></i>
                                </button>
                                <button onclick="deleteProduct({{ $product->id }})" class="p-1">
                                    <i class="fas fa-trash text-red-600"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-3 md:px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-3"></i>
                            <p>Aucun produit trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
        <div class="px-3 md:px-6 py-3 border-t">
            {{ $products->links() }}
        </div>
        @endif
    </div>
   <!-- Modal d'ajout -->
   <div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-start md:items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <!-- Contenu du modal -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl mx-auto transform transition-all">
            <div class="px-4 md:px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg md:text-xl font-bold">
                        <i class="fas fa-plus-circle mr-2 text-indigo-600"></i>Nouveau produit
                    </h3>
                    <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="addProductForm" action="{{ route('products.store') }}" method="POST">
                @csrf
                <div class="p-4 md:p-6 space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Les champs du formulaire -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Nom <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg">
                        </div>
 
                        <div>
                            <label class="block text-sm font-medium mb-1">Catégorie <span class="text-red-500">*</span></label>
                            <select name="category_id" required class="w-full px-3 py-2 border rounded-lg">
                                <option value="">Sélectionner</option>
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

                <div class="px-4 md:px-6 py-4 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3 rounded-b-lg">
                    <button type="button" onclick="closeAddModal()" class="w-full sm:w-auto px-4 py-2 bg-gray-100 rounded-lg">
                        Annuler
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-lg">
                        <i class="fas fa-save mr-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
 </div>

    <!-- Modal d'édition -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-start md:items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
 
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="px-4 md:px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg md:text-xl font-bold">
                        <i class="fas fa-edit mr-2 text-indigo-600"></i>Modifier le produit
                    </h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-4 md:p-6 space-y-4 max-h-[calc(100vh-200px)] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <div class="px-4 md:px-6 py-4 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="w-full sm:w-auto px-4 py-2 bg-gray-100 rounded-lg">
                        Annuler
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-lg">
                        <i class="fas fa-save mr-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
 </div>

<!-- Modal Rapport -->
<div id="reportModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-start md:items-center justify-center p-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="px-4 md:px-6 py-4 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg md:text-xl font-bold">
                        <i class="fas fa-chart-bar mr-2 text-purple-600"></i>Générer un rapport
                    </h3>
                    <button onclick="closeReportModal()" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form action="{{ route('products.generate.report') }}" method="GET" target="_blank">
                <div class="p-4 md:p-6 space-y-4">
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

                    <div class="px-4 md:px-6 py-4 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3">
                        <button type="button" onclick="closeReportModal()" class="w-full sm:w-auto px-4 py-2 bg-gray-100 rounded-lg">
                            Annuler
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-purple-600 text-white rounded-lg">
                            <i class="fas fa-check mr-2"></i>Générer
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
    
        const configs = {
            success: { bgColor: 'bg-green-500', icon: 'fa-check-circle' },
            error: { bgColor: 'bg-red-500', icon: 'fa-exclamation-circle' },
            warning: { bgColor: 'bg-yellow-500', icon: 'fa-exclamation-triangle' }
        };
    
        const config = configs[type];
        content.className = `${config.bgColor} text-white px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300`;
        messageEl.innerHTML = `<i class="fas ${config.icon} mr-2"></i>${message}`;
    
        notification.classList.remove('hidden');
        notification.classList.add('translate-y-0', 'opacity-100');
        
        setTimeout(() => {
            notification.classList.add('-translate-y-full', 'opacity-0');
            setTimeout(() => {
                notification.classList.add('hidden');
                notification.classList.remove('-translate-y-full', 'opacity-0');
            }, 300);
        }, 3000);
    }
};

// Gestion des modales
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
        
        document.getElementById('editForm').action = `/products/${id}`;
        document.getElementById('edit_reference').value = product.reference;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_category_id').value = product.category_id;
        document.getElementById('edit_price').value = product.price;
        document.getElementById('edit_quantity').value = product.quantity;
        document.getElementById('edit_stock_threshold').value = product.stock_threshold;
        document.getElementById('edit_description').value = product.description || '';

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

function openReportModal() {
    document.getElementById('reportModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Support tactile pour mobile
function initTouchEvents() {
    document.querySelectorAll('.modal-content').forEach(modal => {
        let touchStartY = 0;
        
        modal.addEventListener('touchstart', e => {
            touchStartY = e.touches[0].clientY;
        });

        modal.addEventListener('touchmove', e => {
            const touchMoveY = e.touches[0].clientY;
            const diff = touchStartY - touchMoveY;

            if (diff < -50) {
                if (modal.closest('#addModal')) closeAddModal();
                if (modal.closest('#editModal')) closeEditModal();
                if (modal.closest('#reportModal')) closeReportModal();
            }
        });
    });
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

            if (!response.ok) throw new Error('Erreur lors de la suppression');

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
            notificationSystem.show('Erreur lors de la suppression', 'error');
        }
    }
}

// Gestion des formulaires
async function handleFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const isEdit = form.id === 'editForm';
    
    try {
        const formData = new FormData(form);
        if (isEdit) formData.append('_method', 'PUT');

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Erreur lors de l\'opération');

        notificationSystem.show(
            isEdit ? 'Produit mis à jour avec succès' : 'Produit ajouté avec succès',
            'success'
        );
        isEdit ? closeEditModal() : closeAddModal();
        window.location.reload();

    } catch (error) {
        notificationSystem.show(error.message, 'error');
    }
}

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

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Formulaire d'ajout
    const addForm = document.getElementById('addProductForm');
    if (addForm) {
        addForm.addEventListener('submit', handleFormSubmit);
    }

    // Formulaire d'édition
    const editForm = document.getElementById('editForm');
    if (editForm) {
        editForm.addEventListener('submit', handleFormSubmit);
    }

    // Initialiser les événements tactiles
    initTouchEvents();
});

// Gestionnaire d'événements global
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
        closeReportModal();
    }
});
    </script>
    @endpush
@endpush

</div>
@endsection