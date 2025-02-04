<!-- resources/views/invoices/create.blade.php -->
@extends('layouts.app')

@section('title', 'Nouvelle Facture')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('invoices.index') }}" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Créer une nouvelle facture</h2>
        </div>
    </div>

    <form action="{{ route('invoices.store') }}" method="POST">
        @csrf
        
        <!-- Section 1: Informations de la facture -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informations de la facture</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Numéro de facture -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Numéro de facture</label>
                        <input type="text" name="invoice_number" value="{{ $nextInvoiceNumber }}" readonly 
                            class="block w-full p-2.5 border-2 rounded-lg bg-gray-50">
                    </div>

                    <!-- Date de facture -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Date de facture <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required 
                            class="block w-full p-2.5 border-2 rounded-lg">
                    </div>

                    <!-- Date d'échéance -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date d'échéance</label>
                        <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" 
                            class="block w-full p-2.5 border-2 rounded-lg">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Informations du client -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informations du client</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom du client -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Nom du client <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="client_name" required placeholder="Nom complet du client" 
                            class="block w-full p-2.5 border-2 rounded-lg">
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Email du client</label>
                        <input type="email" name="client_email" placeholder="exemple@email.com" 
                            class="block w-full p-2.5 border-2 rounded-lg">
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Téléphone</label>
                        <input type="tel" name="client_phone" placeholder="77 123 45 67" 
                            class="block w-full p-2.5 border-2 rounded-lg">
                    </div>

                    <!-- Adresse -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Adresse</label>
                        <textarea name="client_address" rows="3" placeholder="Adresse complète" 
                            class="block w-full p-2.5 border-2 rounded-lg"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <!-- Section 3: Montants -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Montants</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Sous-total -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Sous-total <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="subtotal" required placeholder="0.00" 
                                class="block w-full p-2.5 border-2 rounded-lg pr-16">
                            <span class="absolute right-3 top-2.5 text-gray-500">FCFA</span>
                        </div>
                    </div>

                    <!-- TVA -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">TVA</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="tax" value="0" placeholder="0.00" 
                                class="block w-full p-2.5 border-2 rounded-lg pr-16">
                            <span class="absolute right-3 top-2.5 text-gray-500">FCFA</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Total <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" name="total" required readonly 
                                class="block w-full p-2.5 border-2 rounded-lg pr-16 bg-gray-50 font-bold">
                            <span class="absolute right-3 top-2.5 text-gray-500">FCFA</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 4: Informations supplémentaires -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informations supplémentaires</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Statut</label>
                        <select name="status" class="block w-full p-2.5 border-2 rounded-lg">
                            <option value="draft">Brouillon</option>
                            <option value="sent">Envoyée</option>
                            <option value="paid">Payée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" rows="3" placeholder="Remarques ou notes supplémentaires" 
                            class="block w-full p-2.5 border-2 rounded-lg"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Boutons d'action -->
        <div class="flex justify-end space-x-4 mb-6">
            <a href="{{ route('invoices.index') }}" 
                class="px-6 py-2.5 border-2 rounded-lg font-medium text-gray-700 hover:bg-gray-50">
                Annuler
            </a>
            <button type="submit" 
                class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">
                Créer la facture
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Récupération des éléments
    const subtotalInput = document.querySelector('input[name="subtotal"]');
    const taxInput = document.querySelector('input[name="tax"]');
    const totalInput = document.querySelector('input[name="total"]');

    // Fonction de calcul du total
    function calculateTotal() {
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const tax = parseFloat(taxInput.value) || 0;
        const total = subtotal + tax;
        totalInput.value = total.toFixed(2);
    }

    // Écouteurs d'événements
    subtotalInput.addEventListener('input', calculateTotal);
    taxInput.addEventListener('input', calculateTotal);

    // Calcul initial
    calculateTotal();
});
</script>
@endsection