@extends('layouts.app')

@section('title', 'Gestion des Devis')

@section('content')
<div class="container-fluid px-6 py-6 bg-gray-50">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Gestion des Devis</h2>
            <p class="text-gray-600">{{ $quotations->count() }} devis enregistrés</p>
        </div>
        <button onclick="openQuotationModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
            <i class="fas fa-plus"></i>Nouveau Devis
        </button>
    </div>

    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-4">
            <form action="{{ route('quotations.index') }}" method="GET" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Rechercher par client ou N°..." 
                        class="flex-1 px-4 py-2 border rounded-lg">
                    
                    <select name="status" class="px-4 py-2 border rounded-lg">
                        <option value="">Tous les statuts</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepté</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                    </select>

                    <input type="date" name="start_date" value="{{ request('start_date') }}" 
                        class="px-4 py-2 border rounded-lg">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" 
                        class="px-4 py-2 border rounded-lg">

                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-filter mr-2"></i>Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Devis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($quotations as $quotation)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">{{ $quotation->quotation_number }}</td>
                    <td class="px-6 py-4">{{ $quotation->date->format('d/m/Y') }}</td>
                    <td class="px-6 py-4">
                        <div>{{ $quotation->client_name }}</div>
                        <div class="text-sm text-gray-500">{{ $quotation->client_phone }}</div>
                    </td>
                    <td class="px-6 py-4">{{ number_format($quotation->total, 0, ',', ' ') }} FCFA</td>
                    <td class="px-6 py-4">
                        <span @class([
                            'px-2 py-1 text-xs rounded-full',
                            'bg-yellow-100 text-yellow-800' => $quotation->status === 'pending',
                            'bg-green-100 text-green-800' => $quotation->status === 'accepted',
                            'bg-red-100 text-red-800' => $quotation->status === 'rejected'
                        ])>
                            {{ $quotation->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button onclick="validateQuotation({{ $quotation->id }})" class="text-green-600 hover:text-green-900">
                            <i class="fas fa-check"></i>
                        </button>
                        <a href="{{ route('quotations.pdf', $quotation) }}" class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        <button onclick="editQuotation({{ $quotation->id }})" class="text-yellow-600 hover:text-yellow-900">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteQuotation({{ $quotation->id }})" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Aucun devis trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($quotations->hasPages())
    <div class="mt-4">
        {{ $quotations->links() }}
    </div>
    @endif
</div>

<!-- Modal Devis -->
<div id="quotationModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
   <div class="flex min-h-screen items-center justify-center">
       <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>

       <div class="relative bg-white rounded-lg w-full max-w-4xl">
           <div class="px-6 py-4 border-b flex justify-between items-center">
               <h3 class="text-xl font-semibold text-gray-800">Nouveau Devis</h3>
               <button onclick="closeQuotationModal()" class="text-gray-400 hover:text-gray-500">
                   <i class="fas fa-times"></i>
               </button>
           </div>

           <form id="quotationForm" onsubmit="submitQuotation(event)">
               <div class="p-6 space-y-6">
                   <!-- Informations client -->
                   <div class="grid grid-cols-3 gap-4">
                       <div>
                           <label class="block text-sm font-medium text-gray-700 mb-2">
                               Nom du client <span class="text-red-500">*</span>
                           </label>
                           <input type="text" name="client_name" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                       </div>
                       <div>
                           <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                           <input type="text" name="client_phone"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                       </div>
                       <div>
                           <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                           <input type="email" name="client_email"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                       </div>
                   </div>

                   <!-- Produits -->
                   <div class="border rounded-lg p-4">
                       <div class="flex justify-between items-center mb-4">
                           <h4 class="font-medium">Produits</h4>
                           <button type="button" onclick="addQuotationProduct()"
                               class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                               <i class="fas fa-plus mr-2"></i>Ajouter
                           </button>
                       </div>
                       <div id="quotationProducts"></div>
                   </div>

                   <!-- Totaux -->
                   <div class="grid grid-cols-2 gap-4">
                       <div>
                           <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                           <textarea name="notes" rows="4"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500"></textarea>
                       </div>
                       <div class="bg-gray-50 p-4 rounded-lg">
                           <div class="space-y-2">
                               <div class="flex justify-between">
                                   <span>Sous-total:</span>
                                   <span id="quotationSubtotal">0 FCFA</span>
                               </div>
                               <div class="flex justify-between items-center">
                                   <span>TVA (%):</span>
                                   <input type="number" name="tax" value="0" min="0" max="100"
                                       class="w-20 px-2 py-1 border rounded text-right"
                                       oninput="calculateQuotationTotal()">
                               </div>
                               <div class="flex justify-between font-bold border-t pt-2">
                                   <span>Total:</span>
                                   <span id="quotationTotal">0 FCFA</span>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>

               <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-4 rounded-b-lg">
                   <button type="button" onclick="closeQuotationModal()"
                       class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                       Annuler
                   </button>
                   <button type="submit" id="submitQuotationBtn"
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                       Enregistrer le devis
                   </button>
               </div>
           </form>
       </div>
   </div>
</div>

<!-- Template produit -->
<template id="quotationProductTemplate">
   <div class="product-row grid grid-cols-12 gap-4 items-center mb-4">
       <div class="col-span-5">
           <input type="text" placeholder="Rechercher un produit..."
               class="w-full px-3 py-2 border rounded focus:ring-blue-500"
               oninput="searchQuotationProducts(this)">
           <div class="suggestions hidden absolute z-10 w-full bg-white border rounded-lg shadow-lg"></div>
       </div>
       <div class="col-span-2">
           <input type="number" min="1" value="1"
               class="w-full px-3 py-2 border rounded text-right"
               oninput="updateQuotationRow(this)">
       </div>
       <div class="col-span-3 text-right">
           <div class="font-medium">0 FCFA</div>
           <div class="text-sm text-gray-500"></div>
       </div>
       <div class="col-span-2 text-right">
           <button type="button" onclick="removeQuotationRow(this)" class="text-red-600 hover:text-red-800">
               <i class="fas fa-trash"></i>
           </button>
       </div>
   </div>
</template>

<!-- Correction du modal d'édition -->
<div id="editQuotationModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="relative bg-white rounded-lg w-full max-w-4xl">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">Modifier le Devis</h3>
                <button onclick="closeEditQuotationModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Modifier l'ID du formulaire -->
            <form id="editQuotationForm" onsubmit="submitEditQuotation(event)">
                <div class="p-6 space-y-6">
                    <!-- Informations client -->
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nom du client <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="client_name" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                            <input type="text" name="client_phone"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="client_email"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Produits -->
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-medium">Produits</h4>
                            <button type="button" onclick="addEditQuotationProduct()"
                                class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700">
                                <i class="fas fa-plus mr-2"></i>Ajouter
                            </button>
                        </div>
                        <!-- Corriger l'ID du conteneur -->
                        <div id="editQuotationProducts"></div>
                    </div>

                    <!-- Totaux -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="4"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-blue-500"></textarea>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span>Sous-total:</span>
                                    <!-- Corriger l'ID pour l'édition -->
                                    <span id="editQuotationSubtotal">0 FCFA</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span>TVA (%):</span>
                                    <input type="number" name="tax" value="0" min="0" max="100"
                                        class="w-20 px-2 py-1 border rounded text-right"
                                        oninput="calculateQuotationTotal()">
                                </div>
                                <div class="flex justify-between font-bold border-t pt-2">
                                    <span>Total:</span>
                                    <!-- Corriger l'ID pour l'édition -->
                                    <span id="editQuotationTotal">0 FCFA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-4 rounded-b-lg">
                    <button type="button" onclick="closeEditQuotationModal()"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Mettre à jour le devis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
    <script>
// Variables globales et initialisation
let selectedQuotationProducts = new Set();
let products = @json($products);

function openQuotationModal() {
   document.getElementById('quotationModal').classList.remove('hidden');
   document.body.style.overflow = 'hidden';
   addQuotationProduct();
}

function closeQuotationModal() {
   document.getElementById('quotationModal').classList.add('hidden');
   document.body.style.overflow = 'auto';
   document.getElementById('quotationForm').reset();
   document.getElementById('quotationProducts').innerHTML = '';
   selectedQuotationProducts.clear();
}

function addQuotationProduct() {
   const template = document.getElementById('quotationProductTemplate');
   const container = document.getElementById('quotationProducts');
   container.appendChild(template.content.cloneNode(true));
}

function searchQuotationProducts(input) {
   const searchTerm = input.value.toLowerCase();
   if(searchTerm.length < 2) return;

   const row = input.closest('.product-row');
   const suggestionsDiv = row.querySelector('.suggestions');
   
   const availableProducts = products.filter(p => 
       !selectedQuotationProducts.has(p.id) && 
       (p.name.toLowerCase().includes(searchTerm) || 
        p.reference.toLowerCase().includes(searchTerm))
   );

   suggestionsDiv.innerHTML = availableProducts.map(p => `
       <div class="p-2 hover:bg-gray-100 cursor-pointer" 
            onclick="selectQuotationProduct(this, ${p.id}, '${p.name}', ${p.price})">
           <div class="font-medium">${p.name}</div>
           <div class="text-sm text-gray-500">Réf: ${p.reference}</div>
       </div>
   `).join('');
   
   suggestionsDiv.classList.remove('hidden');
}

function selectQuotationProduct(element, id, name, price) {
    const row = element.closest('.product-row');
    const input = row.querySelector('input[type="text"]');
    const priceDiv = row.querySelector('.col-span-3 .font-medium'); // Ajouté
    const unitPrice = row.querySelector('.col-span-3 .text-sm'); // Modifié
    
    input.value = name;
    input.dataset.id = id;
    input.dataset.price = price;
    
    row.querySelector('.suggestions').classList.add('hidden');
    priceDiv.textContent = `${(price).toLocaleString('fr-FR')} FCFA`; // Ajouté
    unitPrice.textContent = `${price.toLocaleString('fr-FR')} FCFA/unité`;
    
    selectedQuotationProducts.add(id);
    updateQuotationRow(row.querySelector('input[type="number"]'));
}

function updateQuotationRow(input) {
   const row = input.closest('.product-row');
   const price = parseFloat(row.querySelector('input[type="text"]').dataset.price) || 0;
   const quantity = parseInt(input.value) || 0;
   const total = price * quantity;
   
   row.querySelector('.font-medium').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
   calculateQuotationTotal();
}

function removeQuotationRow(button) {
   const row = button.closest('.product-row');
   const productId = row.querySelector('input[type="text"]').dataset.id;
   if(productId) selectedQuotationProducts.delete(parseInt(productId));
   
   row.remove();
   calculateQuotationTotal();
}

function calculateQuotationTotal() {
   let subtotal = 0;
   document.querySelectorAll('.product-row').forEach(row => {
       const price = parseFloat(row.querySelector('input[type="text"]').dataset.price) || 0;
       const quantity = parseInt(row.querySelector('input[type="number"]').value) || 0;
       subtotal += price * quantity;
   });
   
   const taxRate = parseFloat(document.querySelector('[name="tax"]').value) / 100;
   const tax = subtotal * taxRate;
   const total = subtotal + tax;
   
   document.getElementById('quotationSubtotal').textContent = `${subtotal.toLocaleString('fr-FR')} FCFA`;
   document.getElementById('quotationTotal').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
}

async function submitQuotation(event) {
   event.preventDefault();
   const form = event.target;
   const items = [];
   
   document.querySelectorAll('.product-row').forEach(row => {
       const input = row.querySelector('input[type="text"]');
       if(input.dataset.id) {
           items.push({
               product_id: input.dataset.id,
               quantity: row.querySelector('input[type="number"]').value
           });
       }
   });

   const data = {
       client_name: form.client_name.value,
       client_phone: form.client_phone.value,
       client_email: form.client_email.value,
       notes: form.notes.value,
       tax: form.tax.value,
       items: items
   };

   try {
       const response = await fetch('/quotations', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
           },
           body: JSON.stringify(data)
       });

       if(response.ok) {
           closeQuotationModal();
           location.reload();
       } else {
           alert('Erreur lors de la création du devis');
       }
   } catch(error) {
       console.error('Erreur:', error);
       alert('Erreur lors de la création du devis');
   }
}

function deleteQuotation(id) {
        if (confirm('Voulez-vous vraiment supprimer ce devis ?')) {
            fetch(`/quotations/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    response.json().then(data => {
                        alert(data.message || 'Erreur lors de la suppression');
                    });
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur réseau');
            });
        }
    }

function validateQuotation(id) {
   if(confirm('Voulez-vous valider ce devis et créer une vente ?')) {
       fetch(`/quotations/${id}/validate`, {
           method: 'POST',
           headers: {
               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
           }
       })
       .then(response => {
           if(response.ok) {
               alert('Devis validé et converti en vente');
               location.reload();
           } else {
               alert('Erreur lors de la validation');
           }
       });
   }
}

// Gestionnaires d'événements
document.addEventListener('click', function(e) {
   if(!e.target.closest('.product-row')) {
       document.querySelectorAll('.suggestions').forEach(div => {
           div.classList.add('hidden');
       });
   }
});
function addEditQuotationProduct() {
    const template = document.getElementById('quotationProductTemplate');
    const container = document.getElementById('editQuotationProducts');
    container.appendChild(template.content.cloneNode(true));
}

// Modifier les fonctions existantes pour gérer les deux modaux
function updateQuotationRow(input) {
    const row = input.closest('.product-row');
    const price = parseFloat(row.querySelector('input[type="text"]').dataset.price) || 0;
    const quantity = parseInt(input.value) || 0;
    const total = price * quantity;
    
    row.querySelector('.font-medium').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
    calculateQuotationTotal();
}

function calculateQuotationTotal() {
    const activeModal = document.querySelector('#quotationModal:not(.hidden), #editQuotationModal:not(.hidden)');
    
    if (!activeModal) return;

    let subtotal = 0;
    activeModal.querySelectorAll('.product-row').forEach(row => {
        const price = parseFloat(row.querySelector('input[type="text"]').dataset.price) || 0;
        const quantity = parseInt(row.querySelector('input[type="number"]').value) || 0;
        subtotal += price * quantity;
    });
    
    const taxRate = parseFloat(activeModal.querySelector('[name="tax"]').value) / 100;
    const tax = subtotal * taxRate;
    const total = subtotal + tax;
    
    // Gérer les IDs différents pour l'édition
    if (activeModal.id === 'quotationModal') {
        activeModal.querySelector('#quotationSubtotal').textContent = `${subtotal.toLocaleString('fr-FR')} FCFA`;
        activeModal.querySelector('#quotationTotal').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
    } else {
        activeModal.querySelector('#editQuotationSubtotal').textContent = `${subtotal.toLocaleString('fr-FR')} FCFA`;
        activeModal.querySelector('#editQuotationTotal').textContent = `${total.toLocaleString('fr-FR')} FCFA`;
    }
}

</script>

<script>
// Édition d'un devis
let currentEditQuotationId = null;

async function editQuotation(id) {
    try {
        currentEditQuotationId = id;
        const response = await fetch(`/quotations/${id}/edit`);
        
        if (!response.ok) {
            throw new Error('Erreur de chargement des données');
        }
        
        const { quotation, products } = await response.json();
        
        // Mettre à jour les champs
        const editModal = document.getElementById('editQuotationModal');
        editModal.querySelector('[name="client_name"]').value = quotation.client_name;
        editModal.querySelector('[name="client_phone"]').value = quotation.client_phone;
        editModal.querySelector('[name="client_email"]').value = quotation.client_email || '';
        editModal.querySelector('[name="notes"]').value = quotation.notes || '';
        editModal.querySelector('[name="tax"]').value = quotation.tax;

        // Réinitialiser les produits
        const container = editModal.querySelector('#editQuotationProducts');
        container.innerHTML = '';
        selectedQuotationProducts.clear();

        // Ajouter les produits
        quotation.items.forEach(item => {
            addEditQuotationProduct();
            const lastRow = container.lastElementChild;
            const productInput = lastRow.querySelector('input[type="text"]');
            const quantityInput = lastRow.querySelector('input[type="number"]');
            
            productInput.value = item.product.name;
            productInput.dataset.id = item.product_id;
            productInput.dataset.price = item.unit_price;
            quantityInput.value = item.quantity;
            
            selectedQuotationProducts.add(item.product_id.toString());
            
            // Mettre à jour les totaux
            updateQuotationRow(quantityInput);
        });

        openEditQuotationModal();
    } catch (error) {
        console.error('Erreur:', error);
        alert(error.message);
    }
}

function openEditQuotationModal() {
    document.getElementById('editQuotationModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditQuotationModal() {
    document.getElementById('editQuotationModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

async function submitEditQuotation(event) {
    event.preventDefault();
    const form = event.target;
    const items = [];
    
    document.querySelectorAll('#editQuotationProducts .product-row').forEach(row => {
        const input = row.querySelector('input[type="text"]');
        if(input.dataset.id) {
            items.push({
                product_id: input.dataset.id,
                quantity: row.querySelector('input[type="number"]').value
            });
        }
    });

    const data = {
        client_name: form.client_name.value,
        client_phone: form.client_phone.value,
        client_email: form.client_email.value,
        notes: form.notes.value,
        tax: form.tax.value,
        items: items
    };

    try {
        const response = await fetch(`/quotations/${currentEditQuotationId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });

        if(response.ok) {
            closeEditQuotationModal();
            location.reload();
        } else {
            const error = await response.json();
            alert(error.message || 'Erreur lors de la mise à jour');
        }
    } catch(error) {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    }
}

// Initialisation des événements
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('editQuotationForm').addEventListener('submit', submitEditQuotation);
});
</script>
    @endpush
@endsection