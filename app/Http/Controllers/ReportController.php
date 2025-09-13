<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    // Tampilkan laporan stok / modal
    public function index()
    {
        // Ambil semua produk beserta log stok
        $products = Product::with('stocks')->get();

        return Inertia::render('Report/Index', [
            'products' => $products,
        ]);
    }
}
