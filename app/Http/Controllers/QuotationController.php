<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;
use App\Models\Sale;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::query()
            ->when(request('search'), fn($q) => $q->where('client_name', 'like', '%'.request('search').'%')
                ->orWhere('quotation_number', 'like', '%'.request('search').'%'))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('start_date'), fn($q) => $q->whereDate('date', '>=', request('start_date')))
            ->when(request('end_date'), fn($q) => $q->whereDate('date', '<=', request('end_date')))
            ->withCount('items')
            ->latest()
            ->paginate(10);

        $products = Product::select('id', 'name', 'reference', 'price')->get();
        return view('quotations.index', compact('quotations', 'products'));
    }
    public function create()
    {
        $products = Product::select('id', 'name', 'reference', 'price')->get();
        return view('quotations.create', compact('products'));
    }

    public function store(Request $request)
    {
        return DB::transaction(function() use ($request) {
            $quotation = Quotation::create([
                'quotation_number' => 'DEV-' . date('Y') . '-' . str_pad(Quotation::count() + 1, 4, '0', STR_PAD_LEFT),
                'date' => now(),
                'client_name' => $request->client_name,
                'client_phone' => $request->client_phone,
                'client_email' => $request->client_email,
                'notes' => $request->notes,
                'tax' => $request->tax ?? 0,
                'subtotal' => 0,
                'total' => 0
            ]);

            $subtotal = 0;
            foreach($request->items as $item) {
                $product = Product::find($item['product_id']);
                $itemSubtotal = $item['quantity'] * $product->price;
                $subtotal += $itemSubtotal;
                
                $quotation->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $itemSubtotal
                ]);
            }

            $quotation->update([
                'subtotal' => $subtotal,
                'total' => $subtotal * (1 + $quotation->tax/100)
            ]);

            return response()->json(['success' => true, 'quotation_id' => $quotation->id]);
        });
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('items.product');
        $products = Product::select('id', 'name', 'reference', 'price')->get();
        
        return response()->json([
            'quotation' => $quotation,
            'products' => $products
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        return DB::transaction(function() use ($request, $quotation) {
            // Mise à jour des informations de base
            $quotation->update([
                'client_name' => $request->client_name,
                'client_phone' => $request->client_phone,
                'client_email' => $request->client_email,
                'notes' => $request->notes,
                'tax' => $request->tax ?? 0,
            ]);

            // Suppression des anciens items
            $quotation->items()->delete();

            // Calcul des nouveaux totaux
            $subtotal = 0;
            foreach($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemSubtotal = $item['quantity'] * $product->price;
                $subtotal += $itemSubtotal;
                
                $quotation->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $itemSubtotal
                ]);
            }

            // Mise à jour des totaux
            $quotation->update([
                'subtotal' => $subtotal,
                'total' => $subtotal * (1 + $quotation->tax/100)
            ]);

            return response()->json(['success' => true]);
        });
    }

    public function generatePdf(Quotation $quotation)
    {
    $settings = Setting::first();
    $pdf = PDF::loadView('quotations.pdf', compact('quotation', 'settings'));
    return $pdf->download('devis-' . $quotation->quotation_number . '.pdf');
    }

    public function validateQuotation(Quotation $quotation)
    {
        try {
            DB::transaction(function() use ($quotation) {
                // Vérifier d'abord tous les stocks
                foreach ($quotation->items as $item) {
                    $product = Product::findOrFail($item->product_id);
                    if ($product->quantity < $item->quantity) {
                        throw new \Exception("Stock insuffisant pour {$product->name} (Stock: {$product->quantity}, Demande: {$item->quantity})");
                    }
                }

                // Créer la vente seulement si tous les stocks sont OK
                $sale = Sale::create([
                    'sale_number' => 'VTE-' . date('Y') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT),
                    'sale_date' => now(),
                    'client_name' => $quotation->client_name,
                    'client_phone' => $quotation->client_phone,
                    'payment_method' => 'cash',
                    'subtotal' => $quotation->subtotal,
                    'tax' => $quotation->tax,
                    'total' => $quotation->total
                ]);

                // Mettre à jour les stocks et créer les items
                foreach ($quotation->items as $item) {
                    $product = Product::find($item->product_id);
                    $product->decrement('quantity', $item->quantity);
                    
                    $sale->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal
                    ]);
                }

                $quotation->update(['status' => 'accepted']);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }


    // Ajouter dans QuotationController.php
    public function destroy(Quotation $quotation)
        {
            DB::transaction(function () use ($quotation) {
                $quotation->items()->delete();
                $quotation->delete();
            });

            return response()->json(['success' => true]);
        }
}