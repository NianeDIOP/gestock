<div class="p-6">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6 pb-4 border-b">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">
                Vente #{{ $sale->sale_number }}
            </h3>
            <p class="text-sm text-gray-500">
                Date: {{ $sale->sale_date->format('d/m/Y H:i') }}
            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-sm
            @if($sale->payment_method === 'cash') bg-green-100 text-green-800
            @elseif($sale->payment_method === 'card') bg-blue-100 text-blue-800
            @else bg-gray-100 text-gray-800 @endif">
            {{ ucfirst($sale->payment_method) }}
        </span>
    </div>

    <!-- Informations client -->
    <div class="mb-6">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Informations client</h4>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="font-medium">{{ $sale->client_name }}</p>
            @if($sale->client_phone)
                <p class="text-gray-600">{{ $sale->client_phone }}</p>
            @endif
        </div>
    </div>

    <!-- Produits -->
    <div class="mb-6">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Produits</h4>
        <div class="bg-white border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Prix unitaire</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantité</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($sale->items as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $item->product->name }}</div>
                                <div class="text-sm text-gray-500">{{ $item->product->reference }}</div>
                            </td>
                            <td class="px-4 py-3 text-right">{{ number_format($item->unit_price, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-right">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right font-medium">{{ number_format($item->subtotal, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totaux -->
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Sous-total</span>
                <span class="font-medium">{{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">TVA</span>
                <span class="font-medium">{{ number_format($sale->tax, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="flex justify-between text-base pt-2 border-t">
                <span class="font-bold">Total</span>
                <span class="font-bold">{{ number_format($sale->total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>
    </div>

    @if($sale->notes)
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Notes</h4>
            <p class="text-gray-600 bg-gray-50 rounded-lg p-4">{{ $sale->notes }}</p>
        </div>
    @endif
</div>