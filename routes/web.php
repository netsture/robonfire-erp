<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard/login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    
    // Main Dashboard (shared controller, handles role bifurcation)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin Routes
    Route::middleware(['role:Super Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::post('/tenants/{tenant}/update', [TenantController::class, 'update'])->name('tenants.update');
    });

    // Tenant Admin Routes
    Route::middleware(['role:Admin'])->group(function () {
        // Parties CRUD
        Route::get('/parties', [PartyController::class, 'index'])->name('parties.index');
        Route::post('/parties', [PartyController::class, 'store'])->name('parties.store');
        Route::get('/parties/{party}/edit', [PartyController::class, 'edit'])->name('parties.edit');
        Route::post('/parties/{party}/update', [PartyController::class, 'update'])->name('parties.update');
        Route::delete('/parties/{party}', [PartyController::class, 'destroy'])->name('parties.destroy');
        Route::get('/parties-search', [PartyController::class, 'ajaxSearch'])->name('parties.search');

        // Dynamic Purchase CRUD for: inward, outward, returnable_material, returnable_chalan
        Route::get('/purchases/{type}', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{type}/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases/{type}', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{type}/{id}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
        Route::post('/purchases/{type}/{id}/update', [PurchaseController::class, 'update'])->name('purchases.update');
        Route::delete('/purchases/{type}/{id}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });
});

require __DIR__.'/auth.php';
