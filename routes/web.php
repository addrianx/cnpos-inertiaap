<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;


use Inertia\Inertia;

Route::get('/', function () {
    // Kalau user sudah login arahkan ke dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Kalau belum login, arahkan ke halaman login
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ProductController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::get('/stock/adjust', [StockController::class, 'adjustForm'])->name('stock.adjust.form');
    Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::resource('/stores', StoreController::class);
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

});
    




Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
