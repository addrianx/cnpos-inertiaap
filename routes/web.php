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
use App\Http\Controllers\PCAssemblyController;
use App\Http\Controllers\UserController;

use Inertia\Inertia;

Route::get('/manifest.json', function () {
    $manifest = [
        "name" => "POS Toko",
        "short_name" => "POS", 
        "start_url" => "/",
        "display" => "standalone",
        "background_color" => "#ffffff",
        "theme_color" => "#0d6efd",
        "icons" => [
            [
                "src" => "/icons/icon-192x192.png",
                "sizes" => "192x192", 
                "type" => "image/png",
                "purpose" => "any maskable"
            ],
            [
                "src" => "/icons/icon-512x512.png",
                "sizes" => "512x512",
                "type" => "image/png", 
                "purpose" => "any maskable"
            ]
        ]
    ];

    return Response::json($manifest)
        ->header('Content-Type', 'application/json')
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Cache-Control', 'public, max-age=86400');
});

// Service Worker via Laravel Route - SESUAI NAMA FILE  
Route::get('/serviceworker.js', function () {
    $swPath = public_path('serviceworker.js');
    
    if (!File::exists($swPath)) {
        // Create basic service worker jika tidak ada
        $swContent = <<<'EOT'
        const CACHE_NAME = 'pos-cache-v1';
        const urlsToCache = [
            '/',
            '/icons/icon-192x192.png', 
            '/icons/icon-512x512.png',
            '/manifest.json'
        ];

        self.addEventListener('install', (event) => {
            console.log('🚀 Service Worker Installing...');
            event.waitUntil(
                caches.open(CACHE_NAME)
                    .then((cache) => {
                        console.log('📦 Opened cache');
                        return cache.addAll(urlsToCache);
                    })
            );
        });

        self.addEventListener('fetch', (event) => {
            event.respondWith(
                caches.match(event.request)
                    .then((response) => {
                        return response || fetch(event.request);
                    })
            );
        });

        self.addEventListener('activate', (event) => {
            console.log('🔥 Service Worker Activated');
        });
        EOT;
    } else {
        $swContent = File::get($swPath);
    }

    return Response::make($swContent, 200, [
        'Content-Type' => 'application/javascript',
        'Service-Worker-Allowed' => '/',
        'Cache-Control' => 'public, max-age=3600'
    ]);
});

// Icons route
Route::get('/icons/{file}', function ($file) {
    $path = public_path("icons/{$file}");
    
    if (!File::exists($path)) {
        abort(404);
    }

    return Response::file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=2592000',
        'Access-Control-Allow-Origin' => '*'
    ]);
});

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
    
    // ✅ PRODUCT ROUTES - TAMBAHKAN ROUTE CHECK-SIMILAR DI SINI
    Route::resource('products', ProductController::class);
    Route::post('/products/check-similar', [ProductController::class, 'checkSimilarProducts'])->name('products.check-similar');
    Route::get('/products/generate-sku', [ProductController::class, 'generateSku'])->name('products.generate-sku');
    Route::get('/products/debug', [ProductController::class, 'debugProducts'])->name('products.debug');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    
    // SALES ROUTE
    Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store');
    Route::post('/sales/{sale}/return', [SaleController::class, 'returnSale'])->name('sales.return');

    // PC Assembly Routes
    Route::get('/pc-assembly', [PCAssemblyController::class, 'create'])->name('pc-assembly.create');
    Route::post('/pc-assembly', [PCAssemblyController::class, 'store'])->name('pc-assembly.store');
    Route::get('/pc-assembly/history', [PCAssemblyController::class, 'history'])->name('pc-assembly.history');
    Route::get('/pc-assembly/{assembly}', [PCAssemblyController::class, 'show'])->name('pc-assembly.show');

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
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');   

        Route::get('/debug-pc-data', function() {
            $categoryMapping = [
                'Processor' => 'processors',
                'Motherboard' => 'motherboards', 
                'RAM' => 'memories',
                'VGA Card' => 'graphics_cards',
                'SSD' => 'storages',
                'Hard Disk' => 'storages',
                'Power Supply' => 'power_supplies',
                'Casing' => 'cases',
                'CPU Fan' => 'cpu_coolers'
            ];

            // Get products with categories
            $products = \App\Models\Product::with(['category', 'stocks'])
                ->whereHas('category', function($query) use ($categoryMapping) {
                    $query->whereIn('name', array_keys($categoryMapping));
                })
                ->whereHas('stocks', function($query) {
                    $query->where('quantity', '>', 0);
                })
                ->get();

            // Original grouping by category name
            $originalComponents = $products->groupBy('category.name');
            
            // Mapped grouping for AI
            $mappedComponents = [];
            foreach ($products as $product) {
                $originalCategory = $product->category->name;
                $mappedCategory = $categoryMapping[$originalCategory] ?? null;
                
                if ($mappedCategory) {
                    if (!isset($mappedComponents[$mappedCategory])) {
                        $mappedComponents[$mappedCategory] = [];
                    }
                    $mappedComponents[$mappedCategory][] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => $product->price,
                        'discount' => $product->discount,
                        'original_category' => $originalCategory,
                        'mapped_category' => $mappedCategory
                    ];
                }
            }

            return response()->json([
                'category_mapping_used' => $categoryMapping,
                'original_categories_found' => $originalComponents->keys()->toArray(),
                'mapped_categories_result' => array_keys($mappedComponents),
                'products_per_original_category' => $originalComponents->map(function($products) {
                    return $products->count();
                })->toArray(),
                'products_per_mapped_category' => array_map('count', $mappedComponents),
                'sample_products' => $mappedComponents
            ]);
        });
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';