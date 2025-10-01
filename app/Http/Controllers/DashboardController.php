<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller 
{
    // 📌 Tampilkan daftar produk dan stats
    public function index()
    {
        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum mengelola produk.');
        }

        $storeId = $store->id;

        $products = Product::with(['stocks', 'category'])
            ->where('store_id', $storeId)
            ->get();

        $categories = Category::all();

        // Hitung statistik
        $totalProducts = $products->count();
        $totalStock = $products->sum(function($product) {
            return $product->stocks->sum('quantity'); // asumsi relasi stocks punya kolom quantity
        });
        $salesToday = Sale::where('store_id', $storeId)
        ->whereDate('created_at', now())
        ->sum('total');

        $stats = [
            'products'    => $totalProducts,
            'stock'       => $totalStock,
            'sales_today' => $salesToday,
        ];

        return Inertia::render('Dashboard/Index', [ 
            'products'   => $products,
            'categories' => $categories,
            'stats'      => $stats,
        ]);
    }
}
