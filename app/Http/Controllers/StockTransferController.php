<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Stock;
use App\Models\Store;
use App\Models\StockTransfer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StockTransferController extends Controller
{
    /**
     * 📌 Daftar semua transfer stok yang terkait dengan toko user login
     */
    public function index()
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu.');
        }

        $transfers = StockTransfer::with(['product', 'fromStore', 'toStore'])
            ->where(function ($q) use ($store) {
                $q->where('from_store_id', $store->id)
                ->orWhere('to_store_id', $store->id);
            })
            ->latest()
            ->get();

        return Inertia::render('StockTransfers/Index', [
            'transfers' => $transfers
        ]);
    }

    /**
     * 📌 Form transfer stok (opsional jika pakai inertia/vue form)
     */
    public function create()
    {
        $store = auth()->user()->store;

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu.');
        }

        $products = Product::where('store_id', $store->id)->get();
        $stores   = Store::where('id', '!=', $store->id)->get();

        return Inertia::render('StockTransfers/Create', [
            'products' => $products,
            'stores'   => $stores,
        ]);
    }

    /**
     * 📌 Proses transfer stok
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'product_id'  => 'required|exists:products,id',
            'to_store_id' => 'required|exists:stores,id',
            'quantity'    => 'required|integer|min:1',
            'reference'   => 'nullable|string',
            'note'        => 'nullable|string',
        ]);

        $fromStore = auth()->user()->store; // toko pengirim
        $toStore   = Store::findOrFail($request->to_store_id);

        // pastikan produk milik toko pengirim
        $product = Product::where('id', $request->product_id)
            ->where('store_id', $fromStore->id)
            ->firstOrFail();

        // hitung stok
        $totalStock = $product->stocks->reduce(function ($total, $s) {
            if ($s->type === 'in' || $s->type === 'adjustment') return $total + $s->quantity;
            if ($s->type === 'out') return $total - $s->quantity;
            return $total;
        }, 0);

        if ($request->quantity > $totalStock) {
            return back()->with('error', 'Stok tidak cukup untuk dikirim.');
        }

        \DB::transaction(function () use ($product, $fromStore, $toStore, $request) {
            // 1️⃣ Kurangi stok di toko pengirim
            Stock::create([
                'product_id' => $product->id,
                'type'       => 'out',
                'quantity'   => $request->quantity,
                'reference'  => $request->reference,
                'note'       => 'Transfer ke ' . $toStore->name . ' - ' . $request->note,
            ]);

            // 2️⃣ Tambah stok di toko penerima
            $toProduct = Product::firstOrCreate(
                ['sku' => $product->sku, 'store_id' => $toStore->id],
                [
                    'name'     => $product->name,
                    'cost'     => $product->cost,
                    'price'    => $product->price,
                    'discount' => $product->discount,
                ]
            );

            Stock::create([
                'product_id' => $toProduct->id,
                'type'       => 'in',
                'quantity'   => $request->quantity,
                'reference'  => $request->reference,
                'note'       => 'Transfer dari ' . $fromStore->name . ' - ' . $request->note,
            ]);

            // 3️⃣ Catat ke tabel stock_transfers
            StockTransfer::create([
                'product_id'    => $product->id,
                'from_store_id' => $fromStore->id,
                'to_store_id'   => $toStore->id,
                'quantity'      => $request->quantity,
                'reference'     => $request->reference,
                'note'          => $request->note,
                'type'          => 'transfer',
            ]);
        });

        return redirect()->route('stock-transfers.index')
            ->with('success', 'Transfer stok berhasil.');
    }



}
