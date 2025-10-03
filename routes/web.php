<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockLoanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\UserController;

use Inertia\Inertia;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/set-password', [SetPasswordController::class, 'store'])->name('set-password.store');
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    
    // SALES ROUTE
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::post('/sales/{sale}/return', [SaleController::class, 'returnSale'])->name('sales.return');

    // ✅ PERBAIKAN: SEMUA STOCK ROUTES DIPINDAHKAN KE DALAM MIDDLEWARE GROUP
    Route::middleware(['role:admin,manager'])->group(function () {
        Route::post('/products/bulk-delete', [ProductController::class, 'bulkDelete']);
        
        // ✅ STOCK ROUTES - SEMUA DIMASUKKAN KE SINI
        Route::get('/stock', [StockController::class, 'index'])->name('stock.index');
        Route::get('/stock/adjust', [StockController::class, 'adjustForm'])->name('stock.adjust.form');
        Route::post('/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        
        // REPORT ROUTE
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

        // STOCK LOAN ROUTE
        Route::get('/stock-loan', [StockLoanController::class, 'index'])->name('stockloan.index');
        Route::get('/stock-loan/create', [StockLoanController::class, 'create'])->name('stockloan.create');
        Route::get('/stock-loan/products/{store}', [StockLoanController::class, 'getProducts']);
        Route::post('/stock-loan/store', [StockLoanController::class, 'store'])->name('stockloan.store');
        Route::post('/stock-loan/{loan}/approve', [StockLoanController::class, 'approve'])->name('stock-loan.approve');
        Route::post('/stock-loan/{loan}/reject', [StockLoanController::class, 'reject'])->name('stock-loan.reject');
        Route::post('/stock-loan/{loan}/return', [StockLoanController::class, 'returnLoan'])->name('stock-loan.return');

        // USER ROUTE (Admin & Manager bisa akses)
        Route::resource('users', UserController::class);
        
        // STOCK TRANSFER ROUTE
        Route::get('/stock-transfers', [StockTransferController::class, 'index'])->name('stock-transfers.index');
        Route::get('/stock-transfers/create', [StockTransferController::class, 'create'])->name('stock-transfers.create');
        Route::post('/stock-transfers/transfer', [StockTransferController::class, 'transfer'])->name('stock-transfers.transfer');
        
        // REGISTER USER ROUTE
        Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [RegisteredUserController::class, 'store']);

        Route::resource('categories', CategoryController::class);
        Route::post('/categories/quick-add', [CategoryController::class, 'quickAdd'])
            ->name('categories.quickAdd');
    });

    // STORE ROUTE & USER KHUSUS ADMIN
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('/stores', StoreController::class);
        
        // User management khusus admin
        Route::post('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';