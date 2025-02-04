<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use PDF;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function generateReport(Request $request)
    {
        // Valider les paramètres de filtre
        $request->validate([
            'year' => 'required|integer',
            'period' => 'required|in:day,week,month,year',
        ]);

        $year = (int) $request->year; // Convertir en entier
        $period = $request->period;

        // Définir la période en fonction du filtre
        $startDate = Carbon::create($year, 1, 1); // Commence au début de l'année
        $endDate = Carbon::create($year, 12, 31); // Termine à la fin de l'année

        switch ($period) {
            case 'day':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'year':
                $startDate = Carbon::create($year, 1, 1)->startOfYear();
                $endDate = Carbon::create($year, 12, 31)->endOfYear();
                break;
        }

        // Récupérer les données
        $settings = Setting::first();
        $categories = Category::withCount('products')->get();
        $products = Product::with('category')->get();
        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])->get();

        // Calculer les totaux
        $totalSales = $sales->count();
        $totalRevenue = $sales->sum('total');
        $totalProductsSold = $sales->sum(function ($sale) {
            return $sale->items->sum('quantity');
        });

        // Préparer les données pour le PDF
        $data = [
            'startDate' => $startDate->format('d/m/Y'),
            'endDate' => $endDate->format('d/m/Y'),
            'period' => $period,
            'year' => $year,
            'settings' => $settings,
            'categories' => $categories,
            'products' => $products,
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'totalProductsSold' => $totalProductsSold,
            'generatedDate' => now()->format('d/m/Y H:i'),
        ];

        // Générer le PDF
        $pdf = PDF::loadView('reports.sales_report', $data);

        // Télécharger le PDF
        return $pdf->download('rapport_ventes.pdf');
    }
}