<?php

use App\Livewire\Auth\Login;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdvController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ReportsExportController;


Route::get('/', function () {
   return redirect('login');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->name('dashboard');
    Route::get('/pdv', [PdvController::class, 'index'])->name('pdv');

    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductsController::class, 'create'])->name('products.create');
    // adicione store/update/destroy conforme sua implementação

    Route::get('/clients', [ClientsController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientsController::class, 'create'])->name('clients.create');

    Route::get('/financial', [FinancialController::class, 'index'])->name('financial.index');
    Route::get('/financial/create', [FinancialController::class, 'create'])->name('financial.create');

    Route::get('/reports/sales', [ReportsController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/export/sales', [ReportsExportController::class, 'exportSales'])->name('reports.export.sales');
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
