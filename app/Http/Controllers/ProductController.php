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
     * Supprime un produit
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produit supprimé avec succès');
    }

    public function generateReport(Request $request)
    {
        // Récupérer les paramètres
        $period = $request->input('period', 'month');
        $year = $request->input('year', date('Y'));
        $dateRange = $this->getDateRange($period, $year);
        $periodLabel = $this->getPeriodLabel($period, $year);

        // Récupérer les informations de l'entreprise
        $settings = Setting::first();

        // Statistiques des ventes pour la période
        $salesStats = SaleItem::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(
                DB::raw('COUNT(DISTINCT sale_id) as total_sales_count'),
                DB::raw('COALESCE(SUM(quantity), 0) as total_quantity_sold'),
                DB::raw('COALESCE(SUM(subtotal), 0) as total_revenue'),
                DB::raw('COUNT(DISTINCT DATE(created_at)) as total_days')
            )->first();

        // Statistiques des catégories
        $categoryStats = Category::select('categories.*')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('sale_items', function($join) use ($dateRange) {
                $join->on('products.id', '=', 'sale_items.product_id')
                     ->whereBetween('sale_items.created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->groupBy('categories.id')
            ->selectRaw('
                categories.*,
                COUNT(DISTINCT products.id) as product_count,
                COUNT(DISTINCT CASE WHEN products.quantity = 0 THEN products.id END) as out_of_stock_count,
                COUNT(DISTINCT CASE WHEN products.quantity > 0 AND products.quantity <= products.stock_threshold THEN products.id END) as low_stock_count,
                COALESCE(SUM(products.quantity * products.price), 0) as stock_value,
                COALESCE(SUM(sale_items.quantity), 0) as total_quantity_sold,
                COALESCE(SUM(sale_items.subtotal), 0) as total_sales
            ')
            ->get();

        // Produits les plus vendus
        $bestSellingProducts = Product::with('category')
            ->select('products.*')
            ->leftJoin('sale_items', function($join) use ($dateRange) {
                $join->on('products.id', '=', 'sale_items.product_id')
                     ->whereBetween('sale_items.created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->groupBy('products.id')
            ->selectRaw('
                products.*,
                COALESCE(SUM(sale_items.quantity), 0) as sale_items_sum_quantity,
                COALESCE(SUM(sale_items.subtotal), 0) as sale_items_sum_subtotal
            ')
            ->whereRaw('(SELECT COUNT(*) FROM sale_items WHERE sale_items.product_id = products.id) > 0')
            ->orderByRaw('SUM(COALESCE(sale_items.quantity, 0)) DESC')
            ->limit(10)
            ->get();

        // Produits en stock faible
        $lowStockProducts = Product::with('category')
            ->select('products.*')
            ->leftJoin('sale_items', function($join) use ($dateRange) {
                $join->on('products.id', '=', 'sale_items.product_id')
                     ->whereBetween('sale_items.created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->where(function($query) {
                $query->where('products.quantity', 0)
                      ->orWhereRaw('products.quantity <= products.stock_threshold');
            })
            ->groupBy('products.id')
            ->selectRaw('
                products.*,
                COALESCE(SUM(sale_items.quantity), 0) as total_quantity_sold,
                MAX(sale_items.created_at) as last_sale_date
            ')
            ->orderBy('products.quantity')
            ->get();

        // Valeur totale du stock actuel
        $totalStockValue = Product::sum(DB::raw('price * quantity'));

        // Statistiques générales
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
                                   ($salesStats->total_revenue / $salesStats->total_days) : 0
        ];

        // Liste complète des produits avec leurs ventes
        $products = Product::with('category')
            ->select('products.*')
            ->leftJoin('sale_items', function($join) use ($dateRange) {
                $join->on('products.id', '=', 'sale_items.product_id')
                     ->whereBetween('sale_items.created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->groupBy('products.id')
            ->selectRaw('
                products.*,
                COALESCE(SUM(sale_items.quantity), 0) as sale_items_sum_quantity,
                COALESCE(SUM(sale_items.subtotal), 0) as sale_items_sum_subtotal
            ')
            ->orderBy('products.category_id')
            ->orderBy('products.name')
            ->get();

        // Données pour la vue
        $data = compact(
            'settings',
            'totalStockValue',
            'bestSellingProducts',
            'stockStats',
            'products',
            'periodLabel',
            'categoryStats',
            'lowStockProducts'
        );

        // Configuration du PDF
        $pdf = PDF::loadView('products.report', $data);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 150,
            'defaultFont' => 'sans-serif'
        ]);

        // Génération du nom du fichier
        $filename = sprintf(
            'rapport-stocks-%s-%s.pdf',
            Str::slug($periodLabel),
            now()->format('Y-m-d-His')
        );

        return $pdf->stream($filename);
    }

    private function getDateRange($period, $year)
    {
        $now = now();
        
        switch ($period) {
            case 'day':
                return [
                    'start' => $now->startOfDay(),
                    'end' => $now->endOfDay()
                ];
            case 'week':
                return [
                    'start' => $now->startOfWeek(),
                    'end' => $now->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => $now->startOfMonth(),
                    'end' => $now->endOfMonth()
                ];
            case 'year':
                $date = Carbon::create($year);
                return [
                    'start' => $date->startOfYear(),
                    'end' => $date->endOfYear()
                ];
            default:
                return [
                    'start' => $now->startOfMonth(),
                    'end' => $now->endOfMonth()
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

        return response()->json([
            'success' => true,
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