<div class="space-y-6">
    <div class="grid grid-cols-2 gap-6">
        <div>
            <div class="text-gray-600">N° Facture</div>
            <div class="font-semibold">{{ $sale->sale_number }}</div>
        </div>
        <div>
            <div class="text-gray-600">Date</div>
            <div class="font-semibold">{{ $sale->sale_date->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Produit</th>
                    <th class="px-4 py-2 text-center">Quantité</th>
                    <th class="px-4 py-2 text-right">Prix unitaire</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td class="border-t px-4 py-2">{{ $item->product->name }}</td>
                    <td class="border-t px-4 py-2 text-center">{{ $item->quantity }}</td>
                    <td class="border-t px-4 py-2 text-right">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                    <td class="border-t px-4 py-2 text-right">{{ number_format($item->subtotal, 0, ',', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="3" class="px-4 py-2 text-right font-medium">Sous-total:</td>
                    <td class="px-4 py-2 text-right">{{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td colspan="3" class="px-4 py-2 text-right font-medium">TVA:</td>
                    <td class="px-4 py-2 text-right">{{ number_format($sale->tax, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td colspan="3" class="px-4 py-2 text-right font-bold">Total:</td>
                    <td class="px-4 py-2 text-right font-bold">{{ number_format($sale->total, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>