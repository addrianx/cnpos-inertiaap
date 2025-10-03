<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ✅ FIX: Gunakan many-to-many
        $store = Store::whereHas('users', function($q) use ($user) {
            $q->where('users.id', $user->id);
        })->first();

        if (!$store) {
            // kalau user belum punya store, return kosong
            return Inertia::render('Report/Index', [
                'products' => collect([]),
                'message' => 'Anda belum memiliki toko atau tidak memiliki akses ke toko manapun.'
            ]);
        }

        // ✅ FIX: Ambil produk berdasarkan store_id dengan many-to-many authorization
        $products = Product::with(['stocks', 'saleItems'])
            ->where('store_id', $store->id)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'cost' => $product->cost,
                    'stock' => $product->stock, // menggunakan accessor stock
                    'total_sold' => $product->saleItems->sum('quantity'),
                    'total_revenue' => $product->saleItems->sum('subtotal'),
                    'profit' => $product->saleItems->sum('subtotal') - ($product->cost * $product->saleItems->sum('quantity')),
                ];
            });

        return Inertia::render('Report/Index', [
            'products' => $products,
            'store' => $store, // kirim info toko ke frontend
        ]);
    }
}