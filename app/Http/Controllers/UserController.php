<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
{
    $users = User::all();
    return view('users.index', compact('users'));
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'role' => 'required|in:super_admin,admin',
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $request->role,
        'is_active' => true,
    ]);

    return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès');
}

public function toggleStatus(User $user)
{
    if (!auth()->user()->isSuperAdmin()) {
        return response()->json(['success' => false, 'message' => 'Action non autorisée'], 403);
    }

    $user->update([
        'is_active' => !$user->is_active
    ]);

    return response()->json(['success' => true]);
}

public function destroy(User $user)
{
    if (!auth()->user()->isSuperAdmin()) {
        return response()->json(['success' => false, 'message' => 'Action non autorisée'], 403);
    }

    // Empêcher la suppression de l'utilisateur connecté
    if ($user->id === auth()->id()) {
        return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
    }

    $user->delete();

    return response()->json(['success' => true]);
}
}