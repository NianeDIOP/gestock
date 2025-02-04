<?php

namespace App\Http\Controllers;

use App\Models\Supplier; // Utilisez le modèle Supplier, pas Setting
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\Setting;


class SupplierController extends Controller // Nom correct de la classe
{
    /**
     * Affiche la liste des fournisseurs
     */
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Enregistre un nouveau fournisseur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur ajouté avec succès.');
    }

    /**
     * Récupère les données du fournisseur pour l'édition
     */
    public function edit(Supplier $supplier)
    {
        if (request()->expectsJson()) {
            return response()->json($supplier);
        }
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Met à jour le fournisseur
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
            ->with('success', 'Fournisseur mis à jour avec succès.');
    }

    /**
     * Supprime le fournisseur
     */
    public function destroy(Supplier $supplier)
    {
        try {
            $supplier->delete();
            return redirect()->route('suppliers.index')
                ->with('success', 'Fournisseur supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Erreur lors de la suppression du fournisseur.');
        }
    }

    /**
     * Affiche les détails d'un fournisseur
     */
    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }


    public function export() 
    {
        $suppliers = Supplier::all();
        $settings = Setting::first(); // Récupère les informations de l'entreprise
        
        $pdf = \PDF::loadView('suppliers.export', compact('suppliers', 'settings'));
        $pdf->setPaper('A4', 'landscape'); // Format paysage pour plus d'espace
        
        return $pdf->download('liste-fournisseurs.pdf');
    }
}