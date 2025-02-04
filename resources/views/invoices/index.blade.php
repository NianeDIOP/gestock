<!-- resources/views/invoices/index.blade.php -->
@extends('layouts.app')

@section('title', 'Liste des factures')

@section('content')
<div class="container px-6 mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Liste des factures</h2>
        <button onclick="openAddModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i>Nouvelle facture
        </button>
    </div>

    <!-- Tableau des factures -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">N° Facture</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Client</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Statut</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 text-sm">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium">{{ $invoice->client_name }}</div>
                                @if($invoice->client_email)
                                    <div class="text-gray-500 text-xs">{{ $invoice->client_email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">{{ number_format($invoice->total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs rounded-full
                                    @if($invoice->status === 'paid') bg-green-100 text-green-800
                                    @elseif($invoice->status === 'sent') bg-blue-100 text-blue-800
                                    @elseif($invoice->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @switch($invoice->status)
                                        @case('draft') Brouillon @break
                                        @case('sent') Envoyée @break
                                        @case('paid') Payée @break
                                        @case('cancelled') Annulée @break
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                <button onclick="openEditModal({{ $invoice->id }})" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" 
                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucune facture trouvée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Ajout -->
    <div id="addModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <div class="min-h-screen px-4 text-center">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal content -->
            <div class="inline-block w-full max-w-4xl my-8 text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                <form action="{{ route('invoices.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Nouvelle Facture</h3>
                        <button type="button" onclick="closeAddModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="px-6 py-4 h-[calc(100vh-250px)] overflow-y-auto">
                        <!-- Section 1: Informations facture -->
                        <div class="mb-6">
                            <h4 class="text-sm font-bold text-gray-700 mb-4">Informations de base</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">N° Facture</label>
                                    <input type="text" name="invoice_number" value="{{ $nextInvoiceNumber }}" readonly 
                                        class="block w-full px-3 py-2 border rounded-md shadow-sm bg-gray-50">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date facture <span class="text-red-500">*</span></label>
                                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required 
                                        class="block w-full px-3 py-2 border rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date d'échéance</label>
                                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" 
                                        class="block w-full px-3 py-2 border rounded-md shadow-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Client -->
                        <div class="mb-6">
                            <h4 class="text-sm font-bold text-gray-700 mb-4">Informations client</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                                    <input type="text" name="client_name" required 
                                        class="block w-full px-3 py-2 border rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" name="client_email" 
                                        class="block w-full px-3 py-2 border rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                                    <input type="tel" name="client_phone" 
                                        class="block w-full px-3 py-2 border rounded-md shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                                    <textarea name="client_address" rows="2" 
                                        class="block w-full px-3 py-2 border rounded-md shadow-sm"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Montants -->
                        <div>
                            <h4 class="text-sm font-bold text-gray-700 mb-4">Montants</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sous-total <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" name="subtotal" step="0.01" required 
                                            class="block w-full px-3 py-2 border rounded-md shadow-sm pr-12">
                                        <span class="absolute right-3 top-2 text-gray-500">FCFA</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">TVA</label>
                                    <div class="relative">
                                        <input type="number" name="tax" step="0.01" value="0" 
                                            class="block w-full px-3 py-2 border rounded-md shadow-sm pr-12">
                                        <span class="absolute right-3 top-2 text-gray-500">FCFA</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Total <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" name="total" step="0.01" required readonly 
                                            class="block w-full px-3 py-2 border rounded-md shadow-sm pr-12 bg-gray-50">
                                        <span class="absolute right-3 top-2 text-gray-500">FCFA</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t flex justify-end space-x-3">
                        <button type="button" onclick="closeAddModal()" 
                            class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Créer la facture
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit (Structure similaire, à compléter avec le JS pour charger les données) -->
    <div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-modal="true">
        <!-- Contenu similaire au modal d'ajout, avec les champs pré-remplis -->
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Calcul automatique du total
document.addEventListener('DOMContentLoaded', function() {
    const addForm = document.querySelector('#addModal form');
    const subtotalInput = addForm.querySelector('[name="subtotal"]');
    const taxInput = addForm.querySelector('[name="tax"]');
    const totalInput = addForm.querySelector('[name="total"]');

    function calculateTotal() {
        const subtotal = parseFloat(subtotalInput.value) || 0;
        const tax = parseFloat(taxInput.value) || 0;
        totalInput.value = (subtotal + tax).toFixed(2);
    }

    subtotalInput.addEventListener('input', calculateTotal);
    taxInput.addEventListener('input', calculateTotal);
});

// Fermer avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
    }
});
</script>
@endsection