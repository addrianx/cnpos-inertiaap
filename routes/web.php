<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ReportController;

use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ProductController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::get('/stock/adjust', [StockController::class, 'adjustForm'])->name('stock.adjust.form');
    Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
    

Route::middleware('auth')->group(function () {


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
