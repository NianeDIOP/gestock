<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private $settingsPassword = 'niana1990'; // Mot de passe spécifique aux paramètres

    public function index()
    {
        $settings = Setting::first();
        return view('settings.index', compact('settings'));
    }

    public function verifyPassword(Request $request)
    {
        if ($request->password === $this->settingsPassword) {
            session(['settings_verified' => true]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Mot de passe incorrect']);
    }

    public function update(Request $request)
{
    if (!session('settings_verified')) {
        return response()->json([
            'success' => false,
            'message' => 'Vérification requise !'
        ]);
    }

    try {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:40', // Augmenté à 20 caractères pour les numéros internationaux
            'ninea' => 'nullable|string|max:50',
        ], [
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'name.required' => 'Le nom de l\'entreprise est requis.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'ninea.max' => 'Le NINEA ne doit pas dépasser 50 caractères.'
        ]);

        $settings = Setting::firstOrNew();
        $settings->fill($request->all());
        $settings->save();

        session()->forget('settings_verified');
        
        return response()->json([
            'success' => true,
            'message' => 'Modifications sauvegardées avec succès !'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()
        ]);
    }
}
}