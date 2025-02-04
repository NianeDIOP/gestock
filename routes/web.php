<?php

use Illuminate\Support\Facades\Route;
use App\Models\Setting;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController; 
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\QuotationController;

// Page de connexion admin
Route::get('/', function () {
   $settings = Setting::first();
   return view('admin.login', compact('settings'));
})->name('admin.login');

// Vérification des identifiants admin
Route::post('/admin/check', function () {
   $email = request('email');
   $password = request('password');
   if ($email === 'admin@ayibdiop.com' && $password === 'admin123') {
       session(['admin' => true]);
       return redirect()->route('dashboard');
   }
   return back()->withErrors(['email' => 'Identifiants incorrects.']);
})->name('admin.check');

// Déconnexion admin
Route::post('/admin/logout', function () {
   session()->forget('admin');
   return redirect()->route('admin.login');
})->name('admin.logout');

// Routes protégées par le middleware admin
Route::middleware('admin')->group(function () {
   // Tableau de bord
   Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
   Route::get('/dashboard/filter', [DashboardController::class, 'filter'])->name('dashboard.filter');
   

   // Catégories
   Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
   Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
   Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
   Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
   Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
   Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
   Route::get('/categories/export/pdf', [CategoryController::class, 'exportPdf'])->name('categories.export.pdf');
  
   // Produits
  // Fichier routes/web.php
Route::prefix('products')->group(function () {
   // Routes sans paramètre
   Route::get('/', [ProductController::class, 'index'])->name('products.index');
   Route::get('/out-of-stock', [ProductController::class, 'outOfStock'])->name('products.out_of_stock');
   Route::get('/export/pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
   Route::get('/search', [ProductController::class, 'searchProducts'])->name('products.search');
    // Ajoutez la route generate-report ici
    Route::get('/generate-report', [ProductController::class, 'generateReport'])->name('products.generate-report');
  
    // Routes avec paramètre {product} EN DERNIER
   Route::post('/', [ProductController::class, 'store'])->name('products.store');
   Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
   Route::patch('/{product}/threshold', [ProductController::class, 'updateStockThreshold'])->name('products.update-threshold');
   Route::patch('/{product}/stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
   Route::get('/{product}/restock', [ProductController::class, 'showRestockModal'])->name('products.restock.modal');
   Route::post('/{product}/restock', [ProductController::class, 'restock'])->name('products.restock');
   
   // LA ROUTE SHOW DOIT ÊTRE DERNIÈRE
   Route::get('/{product}', [ProductController::class, 'show'])->name('products.show');
   Route::put('/{product}', [ProductController::class, 'update'])->name('products.update');
   
   Route::delete('/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});


   // Factures
   Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
   Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
   Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
   Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
   Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
   Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

   // Ventes
   // Ventes
   Route::resource('sales', SaleController::class); // Celle-ci génère déjà la route show
   Route::resource('sales', SaleController::class)->except(['create']);
   Route::get('/sales/{sale}/pdf', [SaleController::class, 'generatePdf'])->name('sales.pdf');
   Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');


   Route::prefix('suppliers')->group(function () {
      Route::get('/', [SupplierController::class, 'index'])->name('suppliers.index');
      Route::get('/export', [SupplierController::class, 'export'])->name('suppliers.export'); // Ajoutez cette ligne
      Route::resource('suppliers', SupplierController::class)->except(['index']);
  });

   // Paramètres
   // Paramètres
Route::prefix('settings')->group(function () {
   Route::get('/', [SettingsController::class, 'index'])->name('settings');
    Route::post('/verify-password', [SettingsController::class, 'verifyPassword'])->name('settings.verify');
    Route::post('/', [SettingsController::class, 'update'])->name('settings');
});

Route::get('/generate-report', [ReportController::class, 'generateReport']);

Route::prefix('quotations')->group(function () {
   Route::get('/', [QuotationController::class, 'index'])->name('quotations.index');
   Route::post('/', [QuotationController::class, 'store'])->name('quotations.store');
   Route::get('/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
   Route::put('/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
   Route::post('/{quotation}/validate', [QuotationController::class, 'validateQuotation'])->name('quotations.validate');
   Route::get('/{quotation}/pdf', [QuotationController::class, 'generatePdf'])->name('quotations.pdf');
   Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
});

});