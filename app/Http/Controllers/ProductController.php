<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\SupplierProduct;

class ProductController extends Controller
{
    /**
     * Affiche la liste des produits avec filtres
     */
    public function index(Request $request)
    {
        $query = Product::with('category');
    
        // Filtre de recherche
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('reference', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }
    
        // Filtre par catégorie
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
    
        // Filtre par statut de stock
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'rupture':
                    $query->where('quantity', 0);
                    break;
                case 'low':
                    $query->where('quantity', '>', 0)
                          ->whereColumn('quantity', '<=', 'stock_threshold');
                    break;
                case 'sufficient':
                    $query->whereColumn('quantity', '>', 'stock_threshold');
                    break;
            }
        }
    
        $products = $query->latest()->paginate(10);
        $categories = Category::all();
    
        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Enregistre un nouveau produit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock_threshold' => 'required|integer|min:0',
        ]);

        $validated['reference'] = $this->generateReference();

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produit ajouté avec succès');
    }

    /**
 * Génère une référence unique pour le produit
 */
    protected function generateReference()
    {
        $lastProduct = Product::latest()->first();
        
        if (!$lastProduct) {
            return 'PROD00001';
        }
        
        // Extraire le numéro de la référence
        $lastNumber = (int) substr($lastProduct->reference, 4);
        $nextNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        
        return 'PROD' . $nextNumber;
    }
    /**
     * Affiche le formulaire d'édition d'un produit
     */
    public function edit(Product $product)
    {
        return response()->json($product);
    }

    /**
     * Met à jour un produit
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'stock_threshold' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produit mis à jour avec succès');
    }

    /**
     * Affiche les détails d'un produit
     */
    public function show(Product $product)
    {
        return response()->json($product->load('category'));
    }
    /**
     * Supprime un produit
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return response()->json(['success' => true, 'message' => 'Produit supprimé avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression'], 500);
        }
    }
    public function generateReport(Request $request)
{
    // Paramètres de période
    $period = $request->input('period', 'month');
    $year = $request->input('year', date('Y'));
    $dateRange = $this->getDateRange($period, $year);
    $periodLabel = $this->getPeriodLabel($period, $year);

    // Informations entreprise
    $settings = Setting::first();

    // Statistiques ventes période
    $salesStats = SaleItem::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
        ->select(
            DB::raw('COUNT(DISTINCT sale_id) as total_sales_count'),
            DB::raw('COALESCE(SUM(quantity), 0) as total_quantity_sold'),
            DB::raw('COALESCE(SUM(subtotal), 0) as total_revenue'),
            DB::raw('COUNT(DISTINCT DATE(created_at)) as total_days')
        )->first();

    // Méthodes de paiement
    $paymentMethods = Sale::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
        ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
        ->groupBy('payment_method')
        ->get();

    // Catégories
    $categoryStats = Category::select('categories.*')
        ->leftJoin('products', 'categories.id', '=', 'products.category_id')
        ->leftJoin('sale_items', function($join) use ($dateRange) {
            $join->on('products.id', '=', 'sale_items.product_id')
                 ->whereBetween('sale_items.created_at', [$dateRange['start'], $dateRange['end']]);
        })
        ->groupBy(
            'categories.id', 'categories.name', 'categories.description', 
            'categories.created_at', 'categories.updated_at'
        )
        ->selectRaw('
            categories.*,
            COUNT(DISTINCT products.id) as product_count,
            COUNT(DISTINCT CASE WHEN products.quantity = 0 THEN products.id END) as out_of_stock_count,
            COUNT(DISTINCT CASE WHEN products.quantity > 0 AND products.quantity <= products.stock_threshold THEN products.id END) as low_stock_count,
            COALESCE(SUM(products.quantity * products.price), 0) as stock_value,
            COALESCE(SUM(sale_items.quantity), 0) as total_quantity_sold,
            COALESCE(SUM(sale_items.subtotal), 0) as total_sales,
            CASE 
                WHEN COALESCE(SUM(sale_items.subtotal), 0) > 0 
                THEN ((COALESCE(SUM(sale_items.subtotal), 0) - COALESCE(SUM(products.quantity * products.price), 0)) / COALESCE(SUM(sale_items.subtotal), 0)) * 100 
                ELSE 0 
            END as profit_margin
        ')
        ->get();

    // Tous les produits
    $products = Product::with('category')
        ->leftJoin('sale_items', function($join) use ($dateRange) {
            $join->on('products.id', '=', 'sale_items.product_id')
                 ->whereBetween('sale_items.created_at', [$dateRange['start'], $dateRange['end']]);
        })
        ->groupBy(
            'products.id', 'products.name', 'products.reference', 'products.description',
            'products.price', 'products.quantity', 'products.category_id', 'products.stock_threshold',
            'products.created_at', 'products.updated_at', 'products.low_stock_alert'
        )
        ->selectRaw('
            products.*,
            COALESCE(SUM(sale_items.quantity), 0) as total_sold
        ')
        ->get();

    // Meilleurs vendeurs
    $bestSellingProducts = Product::with('category')
        ->leftJoin('sale_items', function($join) use ($dateRange) {
            $join->on('products.id', '=', 'sale_items.product_id')
                 ->whereBetween('sale_items.created_at', [$dateRange['start'], $dateRange['end']]);
        })
        ->groupBy(
            'products.id', 'products.name', 'products.reference', 'products.description',
            'products.price', 'products.quantity', 'products.category_id', 'products.stock_threshold',
            'products.created_at', 'products.updated_at', 'products.low_stock_alert'
        )
        ->selectRaw('
            products.*,
            COALESCE(SUM(sale_items.quantity), 0) as total_sold,
            COALESCE(SUM(sale_items.subtotal), 0) as total_revenue
        ')
        ->orderByRaw('SUM(COALESCE(sale_items.quantity, 0)) DESC')
        ->limit(10)
        ->get();

    // Stock faible
    $lowStockProducts = Product::with('category')
        ->where(function($query) {
            $query->where('quantity', 0)
                  ->orWhereRaw('quantity <= stock_threshold');
        })
        ->orderBy('quantity')
        ->get();

    // Meilleur jour
    $bestDay = Sale::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
        ->selectRaw('DATE(created_at) as date, SUM(total) as total')
        ->groupBy('date')
        ->orderByDesc('total')
        ->first();

    // Valeur stock
    $totalStockValue = Product::sum(DB::raw('price * quantity'));

    // Stats générales
    $stockStats = [
        'total' => Product::count(),
        'out_of_stock' => Product::where('quantity', 0)->count(),
        'low_stock' => Product::where('quantity', '>', 0)
                              ->whereRaw('quantity <= stock_threshold')
                              ->count(),
        'total_sales' => $salesStats->total_revenue ?? 0,
        'total_quantity_sold' => $salesStats->total_quantity_sold ?? 0,
        'total_sales_count' => $salesStats->total_sales_count ?? 0,
        'average_daily_sales' => $salesStats->total_days > 0 ? 
                                 ($salesStats->total_revenue / $salesStats->total_days) : 0,
        'average_sale' => $salesStats->total_sales_count > 0 ?
                         ($salesStats->total_revenue / $salesStats->total_sales_count) : 0
    ];

    // Données vue
    $data = compact(
        'settings',
        'totalStockValue',
        'bestSellingProducts',
        'stockStats',
        'periodLabel',
        'categoryStats',
        'lowStockProducts',
        'paymentMethods',
        'products',
        'bestDay'
    );

    // PDF
    $pdf = PDF::loadView('products.report', $data);
    $pdf->setPaper('A4', 'portrait');
    $pdf->setOptions([
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled' => true,
        'isRemoteEnabled' => true,
        'dpi' => 150,
        'defaultFont' => 'sans-serif'
    ]);

    $filename = sprintf(
        'rapport-stocks-%s-%s.pdf',
        Str::slug($periodLabel),
        now()->format('Y-m-d-His')
    );

    return response()->streamDownload(function() use ($pdf) {
        echo $pdf->stream();
    }, 'rapport-stock.pdf', [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="rapport-stock.pdf"'
    ]);
}

private function getDateRange($period, $year)
{
    switch ($period) {
        case 'day':
            return [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay()
            ];
        case 'week':
            return [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek()
            ];
        case 'month':
            return [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth()
            ];
        case 'year':
            $date = Carbon::create($year);
            return [
                'start' => $date->copy()->startOfYear(),
                'end' => $date->copy()->endOfYear()
            ];
        default:
            return [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth()
            ];
    }
}

private function getPeriodLabel($period, $year)
{
    $now = now();
    
    return match($period) {
        'day' => $now->format('d/m/Y'),
        'week' => 'Semaine ' . $now->weekOfYear . ' ' . $year,
        'month' => ucfirst($now->translatedFormat('F Y')),
        'year' => 'Année ' . $year,
        default => 'Toutes périodes'
    };
}
     
   
  
    public function exportPdf(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('reference', 'like', "%{$request->search}%")
                  ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->get();
        $settings = Setting::first();

        $pdf = PDF::loadView('exports.products-pdf', compact('products', 'settings'));
        return $pdf->download('liste-produits.pdf');
    }

    /**
     * Affiche les produits en rupture de stock
     */
    public function outOfStock(Request $request)
    {
        $query = Product::where('quantity', '<=', 0)
            ->with('category')
            ->latest();

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        $products = $query->paginate(10);
        $categories = Category::all();

        return view('products.out_of_stock', compact('products', 'categories'));
    }

    /**
     * Met à jour le stock d'un produit
     */
    public function updateStock(Request $request, Product $product)
{
    $request->validate([
        'quantity' => 'required|integer|min:1'
    ]);

    $product->increment('quantity', $request->quantity);
    
    // Mettre à jour le statut d'alerte de stock bas
    $product->updateStockAlert();

    return response()->json([
        'success' => true,
        'message' => 'Stock mis à jour avec succès',
        'new_quantity' => $product->quantity
    ]);
}

    /**
     * Recherche de produits (pour l'autocomplétion)
     */
    public function searchProducts(Request $request)
    {
        if (strlen($request->input('query', '')) < 2) {
            return response()->json([]);
        }

        $products = Product::where('name', 'like', "%{$request->query}%")
            ->orWhere('reference', 'like', "%{$request->query}%")
            ->select('id', 'name', 'reference', 'quantity')
            ->get();

        return response()->json($products);
    }
}