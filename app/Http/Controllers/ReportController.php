<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ambil store yg dimiliki user
        $store = \App\Models\Store::where('user_id', $user->id)->first();

        if (!$store) {
            // kalau user belum punya store, return kosong
            return Inertia::render('Report/Index', [
                'products' => collect([]),
            ]);
        }

        // ambil produk berdasarkan store_id
        $products = Product::with('stocks')
            ->where('store_id', $store->id)
            ->get();

        return Inertia::render('Report/Index', [
            'products' => $products,
        ]);
    }

}
