@extends('layouts.app')

@section('title', 'Gestion des Ventes')

@section('content')
<div class="container-fluid px-6 py-6 bg-gray-50">
    <!-- En-tête -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div>
            <h2 class="text-xl md:text-2xl font-bold text-gray-800">Gestion des Ventes</h2>
            <p class="text-sm md:text-base text-gray-600">{{ $sales->total() }} ventes enregistrées</p>
        </div>
        <button onclick="openSaleModal()" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-2">
            <i class="fas fa-plus"></i>Nouvelle Vente
        </button>
    </div>

    <!-- Cartes statistiques -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Carte Ventes Totales -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600">Ventes Totales</p>
                    <p class="text-lg md:text-xl font-bold mt-1 break-words">{{ number_format($totalSales, 0, ',', ' ') }} FCFA</p>
                </div>
                <i class="fas fa-chart-line text-blue-600 text-xl md:text-2xl ml-2"></i>
            </div>
        </div>
    
        <!-- Carte Nouveaux Clients -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600">Nouveaux Clients</p>
                    <p class="text-lg md:text-xl font-bold mt-1 break-words">{{ $newClients }}</p>
                </div>
                <i class="fas fa-users text-green-600 text-xl md:text-2xl ml-2"></i>
            </div>
        </div>
    
        <!-- Carte Paiements par Carte -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600">Paiements par Carte</p>
                    <p class="text-lg md:text-xl font-bold mt-1 break-words">{{ $cardPayments }}</p>
                </div>
                <i class="fas fa-credit-card text-purple-600 text-xl md:text-2xl ml-2"></i>
            </div>
        </div>
    
        <!-- Carte Revenu Mensuel -->
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600">Revenu Mensuel</p>
                    <p class="text-lg md:text-xl font-bold mt-1 break-words">{{ number_format($monthlyRevenue, 0, ',', ' ') }} FCFA</p>
                </div>
                <i class="fas fa-dollar-sign text-orange-600 text-xl md:text-2xl ml-2"></i>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <!-- Section Filtres -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <form action="{{ route('sales.index') }}" method="GET" class="space-y-4">
                <!-- Barre de recherche principale -->
                <div class="flex flex-col gap-4">
                    <div class="relative w-full">
                        <input type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            placeholder="Rechercher une vente..." 
                            class="w-full pl-10 pr-4 py-2 border rounded-lg">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    
                    <!-- Boutons de filtres -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button type="button" 
                                onclick="toggleFilters()" 
                                class="w-full sm:w-auto px-4 py-2 border rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2">
                            <i class="fas fa-filter"></i>Filtres
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Appliquer
                        </button>
                    </div>
                </div>

                <!-- Filtres avancés -->
                <div id="advancedFilters" class="hidden">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <input type="text" 
                            name="sale_number" 
                            value="{{ request('sale_number') }}" 
                            placeholder="N° Facture" 
                            class="w-full px-4 py-2 border rounded-lg">
                        
                        <input type="text" 
                            name="client" 
                            value="{{ request('client') }}" 
                            placeholder="Client" 
                            class="w-full px-4 py-2 border rounded-lg">
                        
                        <input type="date" 
                            name="date_from" 
                            value="{{ request('date_from') }}" 
                            class="w-full px-4 py-2 border rounded-lg">
                        
                        <input type="date" 
                            name="date_to" 
                            value="{{ request('date_to') }}" 
                            class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des ventes -->
   <!-- Tableau des ventes -->
    <!-- Vue mobile (cartes) -->
    <div class="block lg:hidden">
        <div class="space-y-4">
            @forelse ($sales as $sale)
            <div class="bg-white rounded-lg shadow p-4">
                <!-- En-tête de la carte -->
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-sm font-medium text-gray-900">N° {{ $sale->sale_number }}</span>
                        <p class="text-sm text-gray-500">{{ $sale->sale_date->format('d/m/Y H:i') }}</p>
                    </div>
                    <span @class([
                        'px-2 py-1 text-xs font-semibold rounded-full',
                        'bg-green-100 text-green-800' => $sale->payment_method === 'cash',
                        'bg-blue-100 text-blue-800' => $sale->payment_method === 'card',
                        'bg-gray-100 text-gray-800' => $sale->payment_method === 'other'
                    ])>
                        {{ ucfirst($sale->payment_method) }}
                    </span>
                </div>

                <!-- Informations client -->
                <div class="mb-3">
                    <div class="font-medium text-gray-900">{{ $sale->client_name }}</div>
                    @if($sale->client_phone)
                        <div class="text-sm text-gray-500">{{ $sale->client_phone }}</div>
                    @endif
                </div>

                <!-- Total -->
                <div class="mb-3">
                    <div class="text-sm text-gray-500">Total:</div>
                    <div class="font-medium text-gray-900">{{ number_format($sale->total, 0, ',', ' ') }} FCFA</div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-3 border-t">
                    <button onclick="showSaleDetails({{ $sale->id }})" 
                        class="text-blue-600 hover:text-blue-900 p-2">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="openEditModal({{ $sale->id }})" 
                        class="text-gray-600 hover:text-gray-900 p-2">
                        <i class="fas fa-edit"></i>
                    </button>
                    <a href="{{ route('sales.pdf', $sale) }}" 
                        target="_blank" 
                        class="text-gray-600 hover:text-gray-900 p-2">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                    <button onclick="deleteSale({{ $sale->id }})" 
                        class="text-red-600 hover:text-red-900 p-2">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow p-4 text-center text-gray-500">
                Aucune vente trouvée
            </div>
            @endforelse
        </div>
    </div>

    <!-- Vue desktop (tableau) -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Vente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paiement</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $sale->sale_number }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $sale->sale_date->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $sale->client_name }}</div>
                            @if($sale->client_phone)
                                <div class="text-sm text-gray-500">{{ $sale->client_phone }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ number_format($sale->total, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="px-6 py-4">
                            <span @class([
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-green-100 text-green-800' => $sale->payment_method === 'cash',
                                'bg-blue-100 text-blue-800' => $sale->payment_method === 'card',
                                'bg-gray-100 text-gray-800' => $sale->payment_method === 'other'
                            ])>
                                {{ ucfirst($sale->payment_method) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end space-x-3">
                                <button onclick="showSaleDetails({{ $sale->id }})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="openEditModal({{ $sale->id }})" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="{{ route('sales.pdf', $sale) }}" target="_blank" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <button onclick="deleteSale({{ $sale->id }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune vente trouvée</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($sales->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 mt-4 rounded-lg shadow">
        {{ $sales->links() }}
    </div>
    @endif

<!-- Modal Nouvelle Vente -->
<!-- Modal Nouvelle Vente -->
<div id="saleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

        <!-- Contenu Modal -->
        <div class="relative bg-white rounded-lg w-full max-w-4xl">
            <!-- En-tête Modal -->
            <div class="px-4 sm:px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg sm:text-xl font-semibold text-gray-800">Nouvelle Vente</h3>
                <button type="button" onclick="closeSaleModal()" class="text-gray-400 hover:text-gray-500 p-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="saleForm" onsubmit="submitSale(event)">
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <!-- Section Client -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du client</label>
                            <input type="text" name="client_name" 
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                            <input type="text" name="client_phone"
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                        </div>
                    </div>

                    <!-- Section Produits -->
                    <div class="border rounded-lg p-4">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
                            <h4 class="font-medium text-gray-700">Produits <span class="text-red-500">*</span></h4>
                            <button type="button" onclick="addProductRow()" 
                                class="w-full sm:w-auto bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 flex items-center justify-center">
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

                    <!-- Section Totaux -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Sous-total:</span>
                                <span id="subtotal" class="font-medium">0 FCFA</span>
                            </div>
                            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                                <span class="text-gray-600">TVA (%):</span>
                                <input type="number" name="tax_rate" min="0" max="100" value="0" 
                                    class="w-full sm:w-24 px-3 py-2 border-2 border-gray-300 rounded text-right"
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

                    <!-- Section Paiement et Notes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Mode de paiement <span class="text-red-500">*</span>
                            </label>
                            <select name="payment_method" required 
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500">
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
                                class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Pied de Modal -->
                <div class="px-4 sm:px-6 py-4 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3 rounded-b-lg">
                    <button type="button" onclick="closeSaleModal()" 
                        class="w-full sm:w-auto px-4 py-2 border-2 rounded-lg text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" id="submitSaleBtn" disabled
                        class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-check mr-2"></i>Terminer la vente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- Template pour la ligne de produit -->
<!-- Template pour la ligne de produit -->
<template id="productRowTemplate">
    <div class="product-row bg-gray-50 p-3 rounded-lg">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Recherche Produit -->
            <div class="sm:col-span-5">
                <input type="text" 
                    placeholder="Rechercher un produit..."
                    class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500"
                    oninput="searchProducts(this)">
                <div class="product-suggestions hidden mt-1 absolute z-10 w-full sm:w-80 bg-white border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                </div>
            </div>

            <!-- Quantité -->
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600 mb-1 sm:hidden">Quantité</label>
                <input type="number" 
                    name="quantity" 
                    min="1" 
                    value="1" 
                    required
                    class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500 text-right"
                    onchange="updateRowTotal(this)" 
                    onkeyup="updateRowTotal(this)">
            </div>

            <!-- Prix Total -->
            <div class="sm:col-span-3">
                <label class="block text-sm text-gray-600 mb-1 sm:hidden">Total</label>
                <div class="text-right">
                    <span class="row-total block font-medium">0 FCFA</span>
                    <span class="unit-price block text-sm text-gray-500"></span>
                </div>
            </div>

            <!-- Bouton Supprimer -->
            <div class="flex justify-end sm:col-span-2">
                <button type="button" 
                    onclick="removeProductRow(this)"
                    class="text-red-600 hover:text-red-800 p-2">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Template pour la ligne de produit en édition (même structure avec des noms de fonctions différents) -->
<template id="editProductRowTemplate">
    <div class="product-row bg-gray-50 p-3 rounded-lg">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Recherche Produit -->
            <div class="sm:col-span-5">
                <input type="text" 
                    placeholder="Rechercher un produit..."
                    class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500"
                    oninput="searchEditProducts(this)">
                <div class="product-suggestions hidden mt-1 absolute z-10 w-full sm:w-80 bg-white border rounded-lg shadow-lg max-h-48 overflow-y-auto">
                </div>
            </div>

            <!-- Quantité -->
            <div class="sm:col-span-2">
                <label class="block text-sm text-gray-600 mb-1 sm:hidden">Quantité</label>
                <input type="number" 
                    name="quantity" 
                    min="1" 
                    value="1" 
                    required
                    class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500 text-right"
                    onchange="updateEditRowTotal(this)" 
                    onkeyup="updateEditRowTotal(this)">
            </div>

            <!-- Prix Total -->
            <div class="sm:col-span-3">
                <label class="block text-sm text-gray-600 mb-1 sm:hidden">Total</label>
                <div class="text-right">
                    <span class="row-total block font-medium">0 FCFA</span>
                    <span class="unit-price block text-sm text-gray-500"></span>
                </div>
            </div>

            <!-- Bouton Supprimer -->
            <div class="flex justify-end sm:col-span-2">
                <button type="button" 
                    onclick="removeEditProductRow(this)"
                    class="text-red-600 hover:text-red-800 p-2">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Modal Édition Vente -->
<div id="editSaleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        
        <!-- Contenu Modal -->
        <div class="relative bg-white rounded-lg w-full max-w-4xl">
            <form id="editSaleForm" onsubmit="submitEditSale(event)" data-sale-id="">
                <div id="editSaleContent">
                    <!-- En-tête Modal -->
                    <div class="px-4 sm:px-6 py-4 border-b flex justify-between items-center">
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-800">Modifier la Vente</h3>
                        <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500 p-2">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                        <!-- Section Client -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nom du client</label>
                                <input type="text" name="client_name" 
                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                                <input type="text" name="client_phone"
                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
                            </div>
                        </div>

                        <!-- Section Produits -->
                        <div class="border rounded-lg p-4">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
                                <h4 class="font-medium text-gray-700">Produits <span class="text-red-500">*</span></h4>
                                <button type="button" onclick="addEditProductRow()" 
                                    class="w-full sm:w-auto bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i>Ajouter
                                </button>
                            </div>

                            <div id="editProductsContainer" class="space-y-4 max-h-[300px] overflow-y-auto pr-2">
                                <!-- Les produits seront chargés ici -->
                            </div>
                        </div>

                        <!-- Section Totaux -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Sous-total:</span>
                                    <span id="editSubtotal" class="font-medium">0 FCFA</span>
                                </div>
                                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                                    <span class="text-gray-600">TVA (%):</span>
                                    <input type="number" name="tax_rate" min="0" max="100" value="0" 
                                        class="w-full sm:w-24 px-3 py-2 border-2 border-gray-300 rounded text-right"
                                        onchange="calculateEditTotals()" onkeyup="calculateEditTotals()">
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">TVA:</span>
                                    <span id="editTax" class="font-medium">0 FCFA</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t">
                                    <span class="font-bold">Total:</span>
                                    <span id="editTotal" class="font-bold text-xl">0 FCFA</span>
                                </div>
                            </div>
                        </div>

                        <!-- Section Paiement et Notes -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Mode de paiement <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_method" required 
                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500">
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
                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pied de Modal -->
                    <div class="px-4 sm:px-6 py-4 bg-gray-50 flex flex-col sm:flex-row justify-end gap-3 rounded-b-lg">
                        <button type="button" onclick="closeEditModal()" 
                            class="w-full sm:w-auto px-4 py-2 border-2 rounded-lg text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-check mr-2"></i>Mettre à jour
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const globalState = {
        products: @json($products),
        selectedProducts: new Set(),
        selectedEditProducts: new Set()
    };
    
    function toggleFilters() {
        const filters = document.getElementById('advancedFilters');
        filters.classList.toggle('hidden');
    }
    
    // Gestion du modal de vente
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
        globalState.selectedProducts.clear();
        document.getElementById('noProducts').style.display = 'block';
        document.getElementById('submitSaleBtn').disabled = true;
    }
    
    // Gestion des produits
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
    
        const availableProducts = globalState.products.filter(p => 
            !globalState.selectedProducts.has(p.id) && 
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
        const quantityInput = row.querySelector('input[name="quantity"]');
        const unitPrice = row.querySelector('.unit-price');
        
        input.value = name;
        input.dataset.productId = id;
        input.dataset.price = price;
        input.dataset.stock = stock;
    
        quantityInput.max = stock;
        unitPrice.textContent = `${price.toLocaleString('fr-FR')} FCFA/unité`;
    
        row.querySelector('.product-suggestions').classList.add('hidden');
        globalState.selectedProducts.add(id);
    
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
            Swal.fire({
                title: 'Stock insuffisant',
                text: `Le stock disponible est de ${stock} unités`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            input.value = stock;
            return;
        }
        
        const total = price * quantity;
        row.querySelector('.row-total').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
        calculateTotals();
    }
    
    function removeProductRow(button) {
        const row = button.closest('.product-row');
        const productId = row.querySelector('input[type="text"]').dataset.productId;
        if (productId) globalState.selectedProducts.delete(parseInt(productId));
        
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
                const quantity = parseInt(row.querySelector('input[name="quantity"]').value);
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
        
        try {
            const formData = new FormData(event.target);
            const items = [];
            
            document.querySelectorAll('.product-row').forEach(row => {
                const productInput = row.querySelector('input[type="text"]');
                const productId = productInput.dataset.productId;
                if (productId) {
                    items.push({
                        product_id: productId,
                        quantity: row.querySelector('input[name="quantity"]').value
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
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Vente enregistrée avec succès',
                    showConfirmButton: false,
                    timer: 1500
                });
                window.open(`/sales/${result.sale_id}/pdf`, '_blank');
                location.reload();
            } else {
                throw new Error(result.error || 'Erreur lors de l\'enregistrement de la vente');
            }
        } catch (error) {
            console.error('Erreur:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: error.message || 'Une erreur est survenue'
            });
        } finally {
            submitBtn.disabled = false;
        }
    }
    
    function addEditProductRow() {
        const template = document.getElementById('editProductRowTemplate');
        const container = document.getElementById('editProductsContainer');
        const clone = template.content.cloneNode(true);
        container.appendChild(clone);
    }
    
    function searchEditProducts(input) {
        const searchTerm = input.value.toLowerCase();
        const row = input.closest('.product-row');
        const suggestionsDiv = row.querySelector('.product-suggestions');
        
        if (searchTerm.length < 2) {
            suggestionsDiv.classList.add('hidden');
            return;
        }
    
        const availableProducts = globalState.products.filter(p => 
            !globalState.selectedEditProducts.has(p.id) && 
            (p.name.toLowerCase().includes(searchTerm) || 
            p.reference.toLowerCase().includes(searchTerm))
        );
    
        suggestionsDiv.innerHTML = availableProducts.map(p => `
            <div class="suggestion p-2 hover:bg-gray-100 cursor-pointer" 
                onclick="selectEditProduct(this, ${p.id}, '${p.name}', ${p.price}, ${p.quantity})">
                <div class="font-medium">${p.name}</div>
                <div class="text-sm text-gray-500">
                    Ref: ${p.reference} - Stock: ${p.quantity}
                </div>
            </div>
        `).join('');
    
        suggestionsDiv.classList.remove('hidden');
    }
    
    function selectEditProduct(element, id, name, price, stock) {
        const row = element.closest('.product-row');
        const input = row.querySelector('input[type="text"]');
        const quantityInput = row.querySelector('input[name="quantity"]');
        const unitPrice = row.querySelector('.unit-price');
        
        input.value = name;
        input.dataset.productId = id;
        input.dataset.price = price;
        input.dataset.stock = stock;
    
        quantityInput.max = stock;
        unitPrice.textContent = `${price.toLocaleString('fr-FR')} FCFA/unité`;
    
        row.querySelector('.product-suggestions').classList.add('hidden');
        globalState.selectedEditProducts.add(id);
    
        updateEditRowTotal(quantityInput);
    }
    
    function updateEditRowTotal(input) {
        const row = input.closest('.product-row');
        const productInput = row.querySelector('input[type="text"]');
        const quantity = parseInt(input.value);
        const price = parseFloat(productInput.dataset.price);
        const stock = parseInt(productInput.dataset.stock);
        
        if (quantity > stock) {
            Swal.fire({
                title: 'Stock insuffisant',
                text: `Le stock disponible est de ${stock} unités`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            input.value = stock;
            return;
        }
        
        const total = price * quantity;
        row.querySelector('.row-total').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
        calculateEditTotals();
    }
    
    function removeEditProductRow(button) {
        const row = button.closest('.product-row');
        const productId = row.querySelector('input[type="text"]').dataset.productId;
        if (productId) globalState.selectedEditProducts.delete(parseInt(productId));
        
        row.remove();
        calculateEditTotals();
    }
    
    function calculateEditTotals() {
        let subtotal = 0;
        document.querySelectorAll('#editProductsContainer .product-row').forEach(row => {
            const productInput = row.querySelector('input[type="text"]');
            if (productInput.dataset.productId) {
                const price = parseFloat(productInput.dataset.price);
                const quantity = parseInt(row.querySelector('input[name="quantity"]').value);
                subtotal += price * quantity;
            }
        });
        
        const taxRate = parseFloat(document.querySelector('#editSaleForm [name="tax_rate"]').value) / 100;
        const tax = subtotal * taxRate;
        const total = subtotal + tax;
        
        document.getElementById('editSubtotal').textContent = `${subtotal.toLocaleString('fr-FR')} FCFA`;
        document.getElementById('editTax').textContent = `${tax.toLocaleString('fr-FR')} FCFA`;
        document.getElementById('editTotal').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
    }
    
    async function openEditModal(saleId) {
        try {
            const response = await fetch(`/sales/${saleId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) throw new Error('Erreur lors du chargement du formulaire');
            
            const content = await response.text();
            document.getElementById('editSaleContent').innerHTML = content;
            document.getElementById('editSaleModal').classList.remove('hidden');
            document.getElementById('editSaleForm').setAttribute('data-sale-id', saleId);
            document.body.style.overflow = 'hidden';
    
            initializeEditForm();
        } catch (error) {
            console.error('Erreur:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur est survenue lors du chargement du formulaire d\'édition'
            });
        }
    }
    
    function initializeEditForm() {
        document.querySelectorAll('#editProductsContainer .product-row input[type="text"]').forEach(input => {
            if (input.dataset.productId) {
                globalState.selectedEditProducts.add(parseInt(input.dataset.productId));
                input.addEventListener('input', function() {
                    searchEditProducts(this);
                });
            }
        });
    
        document.querySelectorAll('#editProductsContainer input[name="quantity"]').forEach(input => {
            input.addEventListener('change', function() {
                updateEditRowTotal(this);
            });
            input.addEventListener('keyup', function() {
                updateEditRowTotal(this);
            });
        });
    
        const taxInput = document.querySelector('#editSaleForm [name="tax_rate"]');
        if (taxInput) {
            taxInput.addEventListener('change', calculateEditTotals);
            taxInput.addEventListener('keyup', calculateEditTotals);
        }
    
        calculateEditTotals();
    }
    
    function closeEditModal() {
        document.getElementById('editSaleModal').classList.add('hidden');
        document.getElementById('editSaleContent').innerHTML = '';
        document.body.style.overflow = 'auto';
        globalState.selectedEditProducts.clear();
    }
    
    async function submitEditSale(event) {
        event.preventDefault();
        
        const form = event.target;
        const saleId = form.getAttribute('data-sale-id');
        const items = [];
        
        document.querySelectorAll('#editProductsContainer .product-row').forEach(row => {
            const productInput = row.querySelector('input[type="text"]');
            const productId = productInput.dataset.productId;
            if (productId) {
                items.push({
                    product_id: productId,
                    quantity: row.querySelector('input[name="quantity"]').value
                });
            }
        });
        
        const data = {
            client_name: form.elements['client_name'].value,
            client_phone: form.elements['client_phone'].value,
            payment_method: form.elements['payment_method'].value,
            notes: form.elements['notes'].value,
            tax_rate: form.elements['tax_rate'].value,
            items: items
        };
    
        try {
            const response = await fetch(`/sales/${saleId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
               'X-HTTP-Method-Override': 'PUT'
           },
           body: JSON.stringify(data)
       });

       if (response.ok) {
           Swal.fire({
               icon: 'success',
               title: 'Succès',
               text: 'Vente mise à jour avec succès',
               timer: 1500,
               showConfirmButton: false
           });
           closeEditModal();
           location.reload();
       } else {
           const result = await response.json();
           throw new Error(result.message || 'Erreur lors de la mise à jour');
       }
   } catch (error) {
       Swal.fire({
           icon: 'error',
           title: 'Erreur',
           text: error.message
       });
   }
}

function deleteSale(id) {
   Swal.fire({
       title: 'Confirmation',
       text: 'Cette action est irréversible. Continuer ?',
       icon: 'warning',
       showCancelButton: true,
       confirmButtonColor: '#d33',
       cancelButtonColor: '#3085d6',
       confirmButtonText: 'Oui, supprimer',
       cancelButtonText: 'Annuler'
   }).then((result) => {
       if (result.isConfirmed) {
           fetch(`/sales/${id}`, {
               method: 'DELETE',
               headers: {
                   'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                   'Accept': 'application/json'
               }
           })
           .then(response => response.json())
           .then(data => {
               if(data.success) {
                   Swal.fire({
                       icon: 'success',
                       title: 'Succès',
                       text: data.message,
                       timer: 1500,
                       showConfirmButton: false
                   });
                   location.reload();
               } else {
                   throw new Error(data.message);
               }
           })
           .catch(error => {
               Swal.fire({
                   icon: 'error',
                   title: 'Erreur',
                   text: error.message || 'Une erreur est survenue'
               });
           });
       }
   });
}

async function showSaleDetails(saleId) {
   try {
       const response = await fetch(`/sales/${saleId}`, {
           headers: {
               'X-Requested-With': 'XMLHttpRequest'
           }
       });
       
       if (!response.ok) throw new Error('Erreur lors du chargement des détails');
       
       const content = await response.text();
       document.getElementById('showSaleContent').innerHTML = content;
       document.getElementById('showSaleModal').classList.remove('hidden');
       document.body.style.overflow = 'hidden';
   } catch (error) {
       Swal.fire({
           icon: 'error',
           title: 'Erreur',
           text: 'Une erreur est survenue lors du chargement des détails'
       });
   }
}

function closeShowModal() {
   document.getElementById('showSaleModal').classList.add('hidden');
   document.getElementById('showSaleContent').innerHTML = '';
   document.body.style.overflow = 'auto';
}

// Gestionnaires d'événements globaux
document.addEventListener('DOMContentLoaded', function() {
   document.addEventListener('click', function(e) {
       if (!e.target.closest('.product-row')) {
           document.querySelectorAll('.product-suggestions').forEach(div => {
               div.classList.add('hidden');
           });
       }
   });

   document.addEventListener('keydown', function(e) {
       if (e.key === 'Escape') {
           closeShowModal();
           closeEditModal();
           closeSaleModal();
       }
   });

   document.querySelectorAll('#showSaleModal .bg-white, #editSaleModal .bg-white').forEach(modal => {
       modal.addEventListener('click', function(e) {
           e.stopPropagation();
       });
   });
});

async function showSaleDetails(saleId) {
    try {
        // Afficher un loader
        document.getElementById('showSaleContent').innerHTML = `
            <div class="flex items-center justify-center p-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            </div>
        `;
        
        // Afficher le modal
        document.getElementById('showSaleModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Charger les données
        const response = await fetch(`/sales/${saleId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Erreur lors du chargement des détails');
        }

        // Injecter le contenu dans le modal
        const content = await response.text();
        document.getElementById('showSaleContent').innerHTML = content;

    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.message
        });
        closeShowModal();
    }
}

// Fonction pour fermer le modal
function closeShowModal() {
    document.getElementById('showSaleModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('showSaleContent').innerHTML = '';
}
</script>

<!-- Modal de détails de la vente -->
<div id="showSaleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
        <!-- Overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        
        <!-- Contenu Modal -->
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl">
            <div id="showSaleContent">
                <!-- Le contenu sera chargé dynamiquement ici -->
            </div>
            <div class="px-4 sm:px-6 py-4 bg-gray-50 flex justify-end rounded-b-lg">
                <button type="button" onclick="closeShowModal()" 
                    class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>
@endsection