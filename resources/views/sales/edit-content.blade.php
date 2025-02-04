<div class="px-6 py-4 border-b flex justify-between items-center">
    <h3 class="text-xl font-semibold text-gray-800">Modifier la Vente #{{ $sale->sale_number }}</h3>
    <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
        <i class="fas fa-times"></i>
    </button>
</div>

<div class="p-6 space-y-6">
    <!-- Client -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nom du client</label>
            <input type="text" name="client_name" value="{{ $sale->client_name }}" 
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
            <input type="text" name="client_phone" value="{{ $sale->client_phone }}"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">
        </div>
    </div>

    <!-- Produits -->
    <div class="border rounded-lg p-4">
        <div class="flex justify-between items-center mb-4">
            <h4 class="font-medium text-gray-700">Produits</h4>
            <button type="button" onclick="addEditProductRow()" 
                class="bg-blue-600 text-white px-3 py-2 rounded hover:bg-blue-700 flex items-center">
                <i class="fas fa-plus mr-2"></i>Ajouter
            </button>
        </div>

        <div id="editProductsContainer" class="space-y-4">
            @foreach($sale->items as $item)
            <div class="product-row bg-gray-50 p-3 rounded-lg">
                <div class="grid grid-cols-12 gap-3 items-center">
                    <div class="col-span-5">
                        <input type="text" value="{{ $item->product->name }}"
                            class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500"
                            data-product-id="{{ $item->product_id }}"
                            data-price="{{ $item->unit_price }}"
                            data-stock="{{ $item->product->quantity + $item->quantity }}">
                        <div class="product-suggestions hidden"></div>
                    </div>
                    <div class="col-span-2">
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                            class="w-full px-3 py-2 border-2 border-gray-300 rounded focus:border-blue-500 text-right"
                            onchange="updateEditRowTotal(this)" onkeyup="updateEditRowTotal(this)">
                    </div>
                    <div class="col-span-3">
                        <span class="row-total block text-right font-medium">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</span>
                        <span class="unit-price block text-right text-sm text-gray-500">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA/unité</span>
                    </div>
                    <div class="col-span-2 flex justify-end">
                        <button type="button" onclick="removeEditProductRow(this)" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Totaux -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Sous-total:</span>
                <span id="editSubtotal">{{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">TVA (%):</span>
                <input type="number" name="tax_rate" min="0" max="100" 
                    value="{{ ($sale->tax / $sale->subtotal) * 100 }}"
                    class="w-24 px-3 py-2 border-2 border-gray-300 rounded text-right"
                    onchange="calculateEditTotals()" onkeyup="calculateEditTotals()">
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">TVA:</span>
                <span id="editTax">{{ number_format($sale->tax, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t">
                <span class="font-bold">Total:</span>
                <span id="editTotal">{{ number_format($sale->total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    <!-- Paiement et Notes -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Mode de paiement</label>
            <select name="payment_method"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500">
                <option value="cash" {{ $sale->payment_method == 'cash' ? 'selected' : '' }}>Espèces</option>
                <option value="card" {{ $sale->payment_method == 'card' ? 'selected' : '' }}>Wave</option>
                <option value="other" {{ $sale->payment_method == 'other' ? 'selected' : '' }}>Orange Money</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
            <textarea name="notes" rows="1"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-0">{{ $sale->notes }}</textarea>
        </div>
    </div>
</div>

<div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
    <button type="button" onclick="closeEditModal()" 
        class="px-6 py-3 border-2 rounded-lg text-gray-700 hover:bg-gray-50">
        Annuler
    </button>
    <button type="submit" 
    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
    Mettre à jour
</button>
</div>