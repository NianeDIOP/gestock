<div id="showSaleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
        <div class="relative bg-white rounded-lg w-full max-w-2xl">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-xl font-semibold text-gray-800">
                    Détails de la Vente #<span id="showSaleNumber"></span>
                </h3>
                <button onclick="closeShowModal()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6" id="showSaleContent">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <div class="text-sm text-gray-600">Date</div>
                        <div class="font-medium" id="showSaleDate"></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Client</div>
                        <div class="font-medium" id="showClientName"></div>
                        <div class="text-sm text-gray-500" id="showClientPhone"></div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produit</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qté</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Prix Unit.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody id="showSaleItems" class="divide-y divide-gray-200"></tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right font-medium">Sous-total:</td>
                                <td class="px-4 py-2 text-right" id="showSubtotal"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right font-medium">TVA:</td>
                                <td class="px-4 py-2 text-right" id="showTax"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right font-medium">Total:</td>
                                <td class="px-4 py-2 text-right font-bold" id="showTotal"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-gray-600">Mode de Paiement</div>
                        <div class="font-medium mt-1" id="showPaymentMethod"></div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600">Notes</div>
                        <div class="text-gray-700 mt-1" id="showNotes"></div>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3 rounded-b-lg">
                <button onclick="closeShowModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                    Fermer
                </button>
                <button onclick="printSale()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="fas fa-print mr-2"></i>Imprimer
                </button>
            </div>
        </div>
    </div>
</div>