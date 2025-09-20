<?php

// app/Http/Controllers/StockLoanController.php
namespace App\Http\Controllers;

use App\Models\StockLoan;
use App\Models\StockLoanItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class StockLoanController extends Controller
{
    public function index() {
        $loans = StockLoan::with(['fromStore','toStore','items.product'])->latest()->get();
        return inertia('StockLoan/Index', compact('loans'));
    }

    public function create() {
        $stores = Store::all();
        $products = Product::all();
        return inertia('StockLoan/Create', compact('stores','products'));
    }

    public function store(Request $request) {
        $data = $request->validate([
            'from_store_id' => 'required|exists:stores,id',
            'to_store_id'   => 'required|exists:stores,id',
            'loan_date'     => 'required|date',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
        ]);

        $loan = StockLoan::create($data);

        foreach ($data['items'] as $item) {
            StockLoanItem::create([
                'stock_loan_id' => $loan->id,
                'product_id'    => $item['product_id'],
                'quantity'      => $item['quantity'],
            ]);
        }

        return redirect()->route('stock-loans.index')->with('success','Peminjaman stok berhasil dibuat');
    }

    public function show(StockLoan $stockLoan) {
        $stockLoan->load(['fromStore','toStore','items.product']);
        return inertia('StockLoan/Show', compact('stockLoan'));
    }

    public function edit(StockLoan $stockLoan) {
        $stockLoan->load('items');
        $stores = Store::all();
        $products = Product::all();
        return inertia('StockLoan/Edit', compact('stockLoan','stores','products'));
    }

    public function update(Request $request, StockLoan $stockLoan) {
        $data = $request->validate([
            'loan_date'     => 'required|date',
            'status'        => 'required|in:pending,returned,cancelled',
            'notes'         => 'nullable|string',
        ]);

        $stockLoan->update($data);

        return redirect()->route('stock-loans.index')->with('success','Peminjaman stok berhasil diperbarui');
    }

    public function destroy(StockLoan $stockLoan) {
        $stockLoan->delete();
        return redirect()->route('stock-loans.index')->with('success','Peminjaman stok berhasil dihapus');
    }
}
