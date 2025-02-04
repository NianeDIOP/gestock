<?php

// CategoryController.php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Setting;
use PDF;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(10);
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string'
        ]);

        Category::create($request->all());
        return redirect()->route('categories.index')->with('success', 'Catégorie ajoutée avec succès');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'description' => 'nullable|string'
        ]);

        $category->update($request->all());
        return redirect()->route('categories.index')->with('success', 'Catégorie modifiée avec succès');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Catégorie supprimée avec succès');
    }

    public function exportPdf()
    {
        $categories = Category::all();
        $settings = Setting::first();
        $pdf = PDF::loadView('categories.pdf', compact('categories', 'settings'));
        return $pdf->download('categories.pdf');
    }
}