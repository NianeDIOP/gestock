<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int)$request->get('year', date('Y'));
        $period = $request->get('period', 'month');
        
        $dateRange = $this->getDateRange($period, $year);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // Statistiques principales
        $dailyStats = [
            'sales' => Sale::whereBetween('created_at', [$startDate, $endDate])->sum('total'),
            'products_sold' => SaleItem::whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))->sum('quantity'),
            'out_of_stock' => Product::where('quantity', '<=', 5)->count(),
            'invoices' => Sale::whereBetween('created_at', [$startDate, $endDate])->count()
        ];

        // Données graphiques
        return view('dashboard', [
            'dailyStats' => $dailyStats,
            'salesChartData' => $this->getSalesChartData($startDate, $endDate, $period),
            'categoryChartData' => $this->getCategoryChartData($startDate, $endDate),
            'paymentChartData' => $this->getPaymentChartData($startDate, $endDate),
            'topProducts' => $this->getTopProducts($startDate, $endDate),
            'year' => $year,
            'period' => $period
        ]);
    }

    private function getDateRange($period, $year)
    {
        $year = (int)$year; // Conversion forcée
        $now = Carbon::now()->setYear($year);

        return match($period) {
            'day' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay()
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek()
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth()
            ],
            'year' => [
                'start' => Carbon::create($year, 1, 1)->startOfYear(),
                'end' => Carbon::create($year, 12, 31)->endOfYear()
            ]
        };
    }


    private function getSalesChartData($startDate, $endDate, $period)
    {
        $isSQLite = config('database.default') === 'sqlite';
        
        // Ajustement du format et du groupement selon la période
        switch($period) {
            case 'day':
                $groupBy = $isSQLite ? "strftime('%H', created_at)" : "HOUR(created_at)";
                $format = 'H';
                $interval = 'addHour';
                break;
            case 'week':
                $groupBy = $isSQLite ? "strftime('%Y-%m-%d', created_at)" : "DATE(created_at)";
                $format = 'd/m';
                $interval = 'addDay';
                break;
            case 'month':
                $groupBy = $isSQLite ? "strftime('%Y-%m-%d', created_at)" : "DATE(created_at)";
                $format = 'd/m';
                $interval = 'addDay';
                break;
            case 'year':
                $groupBy = $isSQLite ? "strftime('%m', created_at)" : "MONTH(created_at)";
                $format = 'm/Y';
                $interval = 'addMonth';
                break;
        }
    
        $sales = Sale::select(
            DB::raw("$groupBy as period"),
            DB::raw('COALESCE(SUM(total), 0) as total')
        )
        ->whereBetween('created_at', [$startDate, $endDate])
        ->groupBy('period')
        ->orderBy('period')
        ->get()
        ->keyBy('period');
    
        $labels = [];
        $values = [];
        $current = $startDate->copy();
    
        while ($current <= $endDate) {
            $label = $current->format($format);
            $period_key = $isSQLite ? 
                ($period === 'day' ? $current->format('H') : $current->format('Y-m-d')) :
                $current->format($period === 'day' ? 'H' : 'Y-m-d');
                
            $labels[] = $label;
            $values[] = $sales->get($period_key)->total ?? 0;
            $current->$interval();
        }
    
        return [
            'labels' => $labels,
            'sales' => $values
        ];
    }
    private function extractPeriodValue($label, $period)
    {
        return $period === 'day' ? (int)explode(':', $label)[0] : $label;
    }

    private function getCategoryChartData($startDate, $endDate)
    {
        return DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->select(
                'categories.name',
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_sales')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    private function getPaymentChartData($startDate, $endDate)
    {
        return Sale::whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();
    }

    private function getTopProducts($startDate, $endDate)
    {
        return SaleItem::with('product')
            ->whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(fn($item) => [
                'name' => $item->product->name,
                'total_quantity' => $item->total_quantity
            ]);
    }

    public function filter(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $period = $request->get('period', 'month');

        $dateRange = $this->getDateRange($period, $year);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        return response()->json([
            'stats' => [
                'sales' => Sale::whereBetween('created_at', [$startDate, $endDate])->sum('total'),
                'products_sold' => SaleItem::whereHas('sale', fn($q) => $q->whereBetween('created_at', [$startDate, $endDate]))->sum('quantity'),
                'out_of_stock' => Product::where('quantity', '<=', 5)->count(),
                'invoices' => Sale::whereBetween('created_at', [$startDate, $endDate])->count()
            ],
            'charts' => [
                'sales' => $this->getSalesChartData($startDate, $endDate, $period),
                'categories' => $this->getCategoryChartData($startDate, $endDate),
                'payments' => $this->getPaymentChartData($startDate, $endDate),
                'topProducts' => $this->getTopProducts($startDate, $endDate)
            ]
        ]);
    }
}