<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Affiche la liste des factures
     */
    public function index()
    {
        $invoices = Invoice::latest()->paginate(10);
        $nextInvoiceNumber = $this->generateNextInvoiceNumber();
        return view('invoices.index', compact('invoices', 'nextInvoiceNumber'));
    }

    /**
     * Enregistre une nouvelle facture
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'client_name' => 'required|string|max:255',
            'client_address' => 'nullable|string',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid,cancelled',
            'notes' => 'nullable|string'
        ]);

        $invoice = Invoice::create($validated);

        return redirect()->route('invoices.index')
            ->with('success', 'Facture créée avec succès.');
    }

    /**
     * Affiche les détails d'une facture
     */
    public function show(Invoice $invoice)
    {
        if (request()->ajax()) {
            return response()->json($invoice);
        }
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Récupère les données d'une facture pour l'édition
     */
    public function edit(Invoice $invoice)
    {
        if (request()->ajax()) {
            return response()->json($invoice);
        }
        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Met à jour une facture
     */
    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number,'.$invoice->id,
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'client_name' => 'required|string|max:255',
            'client_address' => 'nullable|string',
            'client_phone' => 'nullable|string|max:20',
            'client_email' => 'nullable|email',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:draft,sent,paid,cancelled',
            'notes' => 'nullable|string'
        ]);

        $invoice->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Facture mise à jour avec succès']);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Facture mise à jour avec succès.');
    }

    /**
     * Supprime une facture
     */
    public function destroy(Invoice $invoice)
    {
        try {
            $invoice->delete();
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Facture supprimée avec succès']);
            }
            return redirect()->route('invoices.index')
                ->with('success', 'Facture supprimée avec succès.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression'], 500);
            }
            return redirect()->route('invoices.index')
                ->with('error', 'Erreur lors de la suppression de la facture.');
        }
    }

    /**
     * Change le statut d'une facture
     */
    public function updateStatus(Invoice $invoice, Request $request)
    {
        $request->validate([
            'status' => 'required|in:draft,sent,paid,cancelled'
        ]);

        $invoice->update(['status' => $request->status]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Statut mis à jour avec succès']);
        }

        return redirect()->back()
            ->with('success', 'Statut mis à jour avec succès.');
    }

    /**
     * Génère le prochain numéro de facture
     */
    protected function generateNextInvoiceNumber()
    {
        $lastInvoice = Invoice::latest()->first();
        $currentYear = date('Y');
        
        if (!$lastInvoice) {
            return 'FAC-' . $currentYear . '-0001';
        }

        // Si nous sommes dans une nouvelle année, recommencer à 0001
        $lastYear = substr($lastInvoice->invoice_number, 4, 4);
        if ($lastYear != $currentYear) {
            return 'FAC-' . $currentYear . '-0001';
        }

        $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
        $nextNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return 'FAC-' . $currentYear . '-' . $nextNumber;
    }

    /**
     * Duplique une facture
     */
    public function duplicate(Invoice $invoice)
    {
        $newInvoice = $invoice->replicate();
        $newInvoice->invoice_number = $this->generateNextInvoiceNumber();
        $newInvoice->invoice_date = now();
        $newInvoice->due_date = now()->addDays(30);
        $newInvoice->status = 'draft';
        $newInvoice->save();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Facture dupliquée avec succès']);
        }

        return redirect()->route('invoices.index')
            ->with('success', 'Facture dupliquée avec succès.');
    }
}