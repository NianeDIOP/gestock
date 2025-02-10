<?php

namespace App\Http\Controllers;


use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;  // Ajout de cette ligne
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Barryvdh\DomPDF\Facade\Pdf;


class SaleController extends Controller
{
    // Liste des ventes
    public function index(Request $request)
{
    $query = Sale::with(['items.product']);
    
    // Filtres
    if ($request->filled('sale_number')) {
        $query->where('sale_number', 'like', '%' . $request->sale_number . '%');
    }
    if ($request->filled('client')) {
        $query->where('client_name', 'like', '%' . $request->client . '%');
    }
    if ($request->filled('date_from')) {
        $query->whereDate('sale_date', '>=', $request->date_from);
    }
    if ($request->filled('date_to')) {
        $query->whereDate('sale_date', '<=', $request->date_to);
    }

    // Calcul des statistiques
    $totalSales = Sale::sum('total');
    $newClients = Sale::distinct('client_name')->count();
    $cardPayments = Sale::where('payment_method', 'card')->count();
    $monthlyRevenue = Sale::whereMonth('sale_date', now()->month)
                         ->whereYear('sale_date', now()->year)
                         ->sum('total');

    $sales = $query->latest()->paginate(10);
    $products = Product::orderBy('name')->get();

    return view('sales.index', compact(
        'sales', 
        'products',
        'totalSales',
        'newClients',
        'cardPayments',
        'monthlyRevenue'
    ));
}

    // Enregistrement d'une vente
    public function store(Request $request)
    {
        Log::info('Requête reçue pour enregistrement d\'une vente:', $request->all());

        // Valider les données
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:15',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,other',
            'notes' => 'nullable|string',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        // Commencer une transaction
        try {
            DB::beginTransaction();

            // Créer une nouvelle vente
            $sale = Sale::create([
                'sale_number' => $this->generateSaleNumber(),
                'sale_date' => now(),
                'client_name' => $validated['client_name'],
                'client_phone' => $validated['client_phone'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'payment_status' => 'paid',
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
            ]);

            $subtotal = 0;

            // Traiter les articles de la vente
            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->quantity < $item['quantity']) {
                    throw new \Exception("Stock insuffisant pour le produit : {$product->name}");
                }

                $itemSubtotal = $product->price * $item['quantity'];
                $subtotal += $itemSubtotal;

                // Créer l'article de vente
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $itemSubtotal,
                ]);

                // Réduire le stock du produit
                $product->decrement('quantity', $item['quantity']);
            }

            // Calculer la TVA et le total
            $tax = $subtotal * ($validated['tax_rate'] / 100);
            $total = $subtotal + $tax;

            // Mettre à jour la vente
            $sale->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
            ]);

            DB::commit();

            Log::info('Vente enregistrée avec succès.', ['sale_id' => $sale->id]);

            return response()->json([
                'success' => true,
                'sale_id' => $sale->id,
                'message' => 'Vente enregistrée avec succès.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'enregistrement de la vente:', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }

    // Affichage d'une vente spécifique
    public function show(Sale $sale)
    {
        $sale->load(['items.product']);
        
        if (request()->ajax()) {
            return view('sales.show', compact('sale'))->render();
        }
        
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $sale->load(['items.product']); // Charge les relations nécessaires
        $products = Product::orderBy('name')->get();
        
        if (request()->ajax()) {
            return view('sales.edit-content', compact('sale', 'products'))->render();
        }
        return view('sales.edit', compact('sale', 'products'));
    }

    public function update(Request $request, Sale $sale)
{
    $validated = $request->validate([
        'client_name' => 'required|string|max:255',
        'client_phone' => 'nullable|string|max:15',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'payment_method' => 'required|in:cash,card,other',
        'notes' => 'nullable|string',
        'tax_rate' => 'required|numeric|min:0|max:100',
    ]);

    try {
        DB::beginTransaction();

        // Réinitialiser le stock des anciens produits
        foreach ($sale->items as $item) {
            $product = $item->product;
            $product->increment('quantity', $item->quantity);
        }

        // Supprimer les anciens items
        $sale->items()->delete();

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            
            if ($product->quantity < $item['quantity']) {
                throw new \Exception("Stock insuffisant pour : {$product->name}");
            }

            $itemSubtotal = $product->price * $item['quantity'];
            $subtotal += $itemSubtotal;

            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $itemSubtotal,
            ]);

            $product->decrement('quantity', $item['quantity']);
        }

        $tax = $subtotal * ($validated['tax_rate'] / 100);
        $total = $subtotal + $tax;

        $sale->update([
            'client_name' => $validated['client_name'],
            'client_phone' => $validated['client_phone'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
        ]);

        DB::commit();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('sales.index')->with('success', 'Vente mise à jour avec succès');

    } catch (\Exception $e) {
        DB::rollBack();
        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
        return back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
    }
}

    // Génération du PDF d'une vente
    public function generatePdf(Sale $sale)
    {
        $sale->load('items.product');
        $settings = Setting::first(); // Récupérer les informations de l'entreprise
        $pdf = Pdf::loadView('sales.invoice', compact('sale', 'settings')); // Passer $settings à la vue
        return $pdf->download('facture-' . $sale->sale_number . '.pdf');
    }

    // Génération d'un numéro unique pour chaque vente
    protected function generateSaleNumber()
    {
        $lastSale = Sale::latest()->first();
        $year = date('Y');

        if (!$lastSale) {
            return 'VENTE-' . $year . '-0001';
        }

        $lastNumber = intval(substr($lastSale->sale_number, -4));
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return 'VENTE-' . $year . '-' . $nextNumber;
    }
    public function destroy(Sale $sale)
    {
        try {
            DB::beginTransaction();
            
            // Vérifier les contraintes
            if($sale->items()->exists()) {
                foreach ($sale->items as $item) {
                    $product = $item->product;
                    $product->increment('quantity', $item->quantity);
                    $item->delete();
                }
            }
            
            $sale->delete();
            DB::commit();
            
            return response()->json([
                'success' => true, 
                'message' => 'Vente supprimée avec succès.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "Impossible de supprimer cette vente : " . $e->getMessage()
            ], 422);
        }
    }

    
    public function exportExcel(Request $request)
    {
        try {
            // Construire la requête en fonction de la période
            $period = $request->input('period');
            $query = Sale::with(['items.product']);
    
            // Déterminer la période
            switch ($period) {
                case 'today':
                    $query->whereDate('sale_date', today());
                    $title = "Ventes du " . today()->format('d/m/Y');
                    break;
                case 'yesterday':
                    $query->whereDate('sale_date', today()->subDay());
                    $title = "Ventes du " . today()->subDay()->format('d/m/Y');
                    break;
                case 'this_week':
                    $query->whereBetween('sale_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    $title = "Ventes de la semaine en cours";
                    break;
                case 'last_week':
                    $query->whereBetween('sale_date', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()]);
                    $title = "Ventes de la semaine dernière";
                    break;
                case 'this_month':
                    $query->whereMonth('sale_date', now()->month);
                    $title = "Ventes du mois de " . now()->format('F Y');
                    break;
                case 'last_month':
                    $query->whereMonth('sale_date', now()->subMonth()->month);
                    $title = "Ventes du mois de " . now()->subMonth()->format('F Y');
                    break;
                case 'custom':
                    if ($request->filled(['start_date', 'end_date'])) {
                        $query->whereBetween('sale_date', [$request->start_date, $request->end_date]);
                        $title = "Ventes du " . \Carbon\Carbon::parse($request->start_date)->format('d/m/Y') . 
                                " au " . \Carbon\Carbon::parse($request->end_date)->format('d/m/Y');
                    }
                    break;
            }
    
            $sales = $query->get();
    
            // Créer un nouveau fichier Excel
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
    
            // En-tête du document
            $sheet->mergeCells('A1:G1');
            $sheet->setCellValue('A1', $title);
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
            // En-têtes des colonnes
            $headers = [
                'A2' => 'N° Vente',
                'B2' => 'Date',
                'C2' => 'Client',
                'D2' => 'Téléphone',
                'E2' => 'Mode de paiement',
                'F2' => 'Total HT',
                'G2' => 'Total TTC'
            ];
    
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
    
            // Style des en-têtes
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E2E8F0']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ];
            $sheet->getStyle('A2:G2')->applyFromArray($headerStyle);
    
            // Remplir les données
            $row = 3;
            foreach ($sales as $sale) {
                $sheet->setCellValue('A' . $row, $sale->sale_number);
                $sheet->setCellValue('B' . $row, $sale->sale_date->format('d/m/Y H:i'));
                $sheet->setCellValue('C' . $row, $sale->client_name);
                $sheet->setCellValue('D' . $row, $sale->client_phone);
                $sheet->setCellValue('E' . $row, ucfirst($sale->payment_method));
                $sheet->setCellValue('F' . $row, $sale->subtotal);
                $sheet->setCellValue('G' . $row, $sale->total);
                $row++;
            }
    
            // Formatage des colonnes
            $sheet->getColumnDimension('A')->setWidth(15);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(25);
            $sheet->getColumnDimension('D')->setWidth(15);
            $sheet->getColumnDimension('E')->setWidth(15);
            $sheet->getColumnDimension('F')->setWidth(15);
            $sheet->getColumnDimension('G')->setWidth(15);
    
            // Format monétaire pour les colonnes de montants
            $lastRow = $row - 1;
            $sheet->getStyle("F3:G$lastRow")->getNumberFormat()->setFormatCode('#,##0.00 "FCFA"');
    
            // Totaux
            $row++;
            $sheet->mergeCells("A$row:E$row");
            $sheet->setCellValue("A$row", 'TOTAL');
            $sheet->setCellValue("F$row", "=SUM(F3:F$lastRow)");
            $sheet->setCellValue("G$row", "=SUM(G3:G$lastRow)");
            $sheet->getStyle("A$row:G$row")->getFont()->setBold(true);
            $sheet->getStyle("F$row:G$row")->getNumberFormat()->setFormatCode('#,##0.00 "FCFA"');
    
            // Style global
            $sheet->getStyle("A3:G$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    
            // Créer la réponse
            $fileName = Str::slug($title) . '.xlsx';
            $writer = new Xlsx($spreadsheet);
    
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
    
            $writer->save('php://output');
            exit;
    
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'exportation : ' . $e->getMessage());
        }
    }
}
