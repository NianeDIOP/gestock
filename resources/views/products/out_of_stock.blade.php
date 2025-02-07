<!-- resources/views/products/out-of-stock.blade.php -->
@extends('layouts.app')

@section('title', 'Produits en rupture de stock')

@section('content')
<div class="container-fluid px-4 py-6 bg-gray-50 min-h-screen">
    <!-- En-tête -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Produits en rupture de stock</h1>
            <p class="text-sm text-gray-600">Liste des produits actuellement en rupture de stock</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('products.index') }}" 
               class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 whitespace-nowrap">
                <i class="fas fa-arrow-left mr-2"></i>Retour aux produits
            </a>
            <!-- Filtre par catégorie -->
            <select id="categoryFilter" class="px-4 py-2 bg-white border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <!-- Bouton de réapprovisionnement -->
            <button onclick="openRestockModal()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 whitespace-nowrap">
                <i class="fas fa-boxes mr-2"></i>Réapprovisionner
            </button>
        </div>
    </div>

    <!-- Tableau des produits -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catégorie</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50 cursor-pointer" onclick="showProduct({{ $product->id }})">
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $product->reference }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category->name ?? '-' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded-full">
                                    Rupture
                                </span>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="event.stopPropagation(); openRestockModal({{ $product->id }})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-boxes"></i>
                                </button>
                                <a href="{{ route('products.show', $product->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-4 text-center text-gray-500">Aucun produit en rupture de stock</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 border-t">
            {{ $products->links() }}
        </div>
    </div>
</div>

<!-- Modal de réapprovisionnement -->
<div id="restockModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Modal content -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Réapprovisionner un produit</h3>
                <button onclick="closeRestockModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="restockForm" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantité à ajouter <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" id="quantity" required min="1" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3 rounded-b-lg">
                    <button type="button" onclick="closeRestockModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Annuler
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de détail du produit -->
<div id="productModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Détails du produit</h3>
                <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Référence</label>
                        <p id="modal-reference" class="mt-1 text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Nom</label>
                        <p id="modal-name" class="mt-1 text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Catégorie</label>
                        <p id="modal-category" class="mt-1 text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Prix</label>
                        <p id="modal-price" class="mt-1 text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Stock actuel</label>
                        <p id="modal-quantity" class="mt-1 text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600">Seuil d'alerte</label>
                        <p id="modal-threshold" class="mt-1 text-gray-900"></p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-600">Description</label>
                    <p id="modal-description" class="mt-1 text-gray-900"></p>
                </div>
            </div>

            <div class="px-6 py-4 border-t bg-gray-50 flex justify-end">
                <button type="button" onclick="closeProductModal()" 
                    class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openRestockModal(productId = null) {
    const modal = document.getElementById('restockModal');
    const form = document.getElementById('restockForm');

    if (productId) {
        form.action = `/products/${productId}/restock`;
    } else {
        form.action = '/products/restock';
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeRestockModal() {
    document.getElementById('restockModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.getElementById('categoryFilter').addEventListener('change', function() {
    const categoryId = this.value;
    window.location.href = `/products/out-of-stock?category=${categoryId}`;
});

async function showProduct(productId) {
    try {
        const response = await fetch(`/products/${productId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) throw new Error(`Erreur ${response.status}: ${response.statusText}`);
        
        const product = await response.json();

        // Remplissage de la modale
        document.getElementById('modal-reference').textContent = product.reference;
        document.getElementById('modal-name').textContent = product.name;
        document.getElementById('modal-category').textContent = product.category?.name || '-';
        document.getElementById('modal-price').textContent = `${parseFloat(product.price).toFixed(2).replace('.', ',')} FCFA`;
        document.getElementById('modal-quantity').textContent = product.quantity;
        document.getElementById('modal-threshold').textContent = product.stock_threshold;
        document.getElementById('modal-description').textContent = product.description || 'Aucune description';

        // Affichage de la modale
        const modal = document.getElementById('productModal');
        modal.classList.remove('hidden');
        modal.style.display = 'block';
        document.body.classList.add('overflow-hidden');

    } catch (error) {
        console.error('Échec:', error);
        alert(`Erreur : ${error.message}`);
    }
}

function closeProductModal() {
    const modal = document.getElementById('productModal');
    modal.classList.add('hidden');
    modal.style.display = 'none';
    document.body.classList.remove('overflow-hidden');
}

// Empêche la propagation du clic sur les boutons d'action
document.querySelectorAll('td.actions button, td.actions a').forEach(element => {
    element.addEventListener('click', event => event.stopPropagation());
});
</script>
@endpush
@endsection