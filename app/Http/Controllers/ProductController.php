<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    // 📌 Tampilkan daftar produk
    public function index()
    {
        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum mengelola produk.');
        }

        $storeId = $store->id;

        // ✅ UPDATE: Eager load user yang membuat produk
        $products = Product::with(['stocks', 'category', 'createdBy'])
            ->where('store_id', $storeId)
            ->get();

        $categories = Category::all();

        return Inertia::render('Products/Index', [
            'products'   => $products,
            'categories' => $categories,
        ]);
    }


    // 📌 Form tambah produk
    public function create()
    {
        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('stores.create')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum mengelola produk.');
        }

        $storeId = $store->id;

        // Ambil semua kategori
        $categories = Category::all();

        // Generate SKU otomatis
        $storeName = $store->name;
        $sku = $this->generateSku($storeName);

        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'autoSku' => $sku,
        ]);
    }

    // 📌 Simpan produk baru (dengan pengecekan ketat)
    public function store(Request $request)
    {
        $request->validate([
            'sku'         => 'required|unique:products',
            'name'        => 'required',
            'cost'        => 'required|numeric',
            'price'       => 'required|numeric',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('products.index')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum menambahkan produk.');
        }

        $productName = strtoupper(trim($request->name));

        // DEBUG
        \Log::info('Store: Checking for existing products with name: ' . $productName);

        // Extract kata kunci penting
        $searchWords = $this->extractImportantWords($productName);
        \Log::info('Store: Search words extracted: ' . implode(', ', $searchWords));

        // CEK KETAT: Cari produk dengan nama yang mirip berdasarkan kata kunci
        $existingProducts = Product::where('store_id', $store->id)
            ->where(function($query) use ($productName, $searchWords) {
                // 1. Exact match - sama persis
                $query->orWhere('name', 'LIKE', $productName);
                
                // 2. Contains match
                $query->orWhere('name', 'LIKE', '%' . $productName . '%');
                
                // 3. DB mengandung nama input
                $query->orWhereRaw('? LIKE CONCAT("%", name, "%")', [$productName]);
                
                // 4. MATCH BY INDIVIDUAL WORDS - PERBAIKAN UTAMA
                foreach ($searchWords as $word) {
                    if (strlen($word) > 2) {
                        $query->orWhere('name', 'LIKE', '%' . $word . '%');
                    }
                }
            })
            ->select('id', 'name', 'sku')
            ->get();

        // DEBUG
        \Log::info('Store: Found ' . $existingProducts->count() . ' existing products');

        if ($existingProducts->count() > 0) {
            $errorMessage = 'Produk dengan nama serupa sudah ada: ';
            $productList = $existingProducts->map(function($product) {
                return $product->name . ' (SKU: ' . $product->sku . ')';
            })->implode(', ');
            
            return back()->withErrors([
                'name' => $errorMessage . $productList
            ]);
        }

        // ✅ Simpan produk
        Product::create([
            'sku'         => $request->sku,
            'name'        => $request->name,
            'cost'        => $request->cost,
            'price'       => $request->price,
            'discount'    => $request->discount ?? 0,
            'store_id'    => $store->id,
            'category_id' => $request->category_id,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }

    // 📌 Method untuk cek produk serupa
    public function checkSimilarProducts(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $store = auth()->user()->stores()->first();
        
        if (!$store) {
            return response()->json(['similar_products' => []]);
        }

        $productName = strtoupper(trim($request->name));
        
        // Jika nama produk kosong, return empty
        if (empty($productName)) {
            return response()->json(['similar_products' => []]);
        }

        // DEBUG: Log untuk melihat apa yang dicari
        \Log::info('Checking similar products for: ' . $productName);

        // Split nama produk menjadi kata-kata individual
        $searchWords = $this->extractImportantWords($productName);
        
        // DEBUG: Log kata kunci yang dicari
        \Log::info('Search words extracted: ' . implode(', ', $searchWords));

        // Jika tidak ada kata kunci penting, return empty
        if (empty($searchWords)) {
            return response()->json(['similar_products' => []]);
        }

        // Cari produk dengan nama yang mirip berdasarkan kata kunci
        $similarProducts = Product::where('store_id', $store->id)
            ->where(function($query) use ($productName, $searchWords) {
                // 1. Exact match - sama persis
                $query->orWhere('name', 'LIKE', $productName);
                
                // 2. Contains match - nama input mengandung nama di DB
                $query->orWhere('name', 'LIKE', '%' . $productName . '%');
                
                // 3. DB mengandung nama input
                $query->orWhereRaw('? LIKE CONCAT("%", name, "%")', [$productName]);
                
                // 4. MATCH BY INDIVIDUAL WORDS - PERBAIKAN UTAMA
                foreach ($searchWords as $word) {
                    if (strlen($word) > 2) { // Hanya kata dengan panjang > 2 karakter
                        $query->orWhere('name', 'LIKE', '%' . $word . '%');
                    }
                }
                
                // 5. REVERSE MATCH - cek jika nama di DB mengandung kata kunci dari input
                foreach ($searchWords as $word) {
                    if (strlen($word) > 2) {
                        $query->orWhereRaw('name LIKE ?', ['%' . $word . '%']);
                    }
                }
            })
            ->select('id', 'name', 'sku')
            ->limit(10)
            ->get();

        // DEBUG: Log hasil pencarian
        \Log::info('Found ' . $similarProducts->count() . ' similar products');

        return response()->json([
            'similar_products' => $similarProducts,
            'search_term' => $productName,
            'search_words' => $searchWords,
            'debug' => [
                'store_id' => $store->id,
                'product_count' => $similarProducts->count(),
                'search_words_used' => $searchWords
            ]
        ]);
    }

    // 📌 Fungsi untuk extract kata-kata penting dari nama produk
    private function extractImportantWords($productName)
    {
        // Kata-kata umum yang sering ada di nama produk tapi tidak penting untuk matching
        $commonWords = [
            'SSD', 'HDD', 'USB', 'RAM', 'PROCESSOR', 'VGA', 'MONITOR', 'KEYBOARD', 'MOUSE',
            'LAPTOP', 'PC', 'KOMPUTER', 'HP', 'SMARTPHONE', 'TABLET',
            'GB', 'MB', 'TB', 'GHZ', 'MHZ', 'RPM',
            'SATA', 'NVME', 'M2', 'PCI', 'PCI-E',
            'WIFI', 'BLUETOOTH', 'LAN', 'HDMI', 'USB', 'VGA', 'DISPLAYPORT',
            'WARNA', 'COLOR', 'SIZE', 'UKURAN', 'TYPE', 'TIPE', 'MODEL',
            'BARU', 'NEW', 'ORIGINAL', 'ORI', 'GENUINE',
            'DAN', 'ATAU', 'DENGAN', 'UNTUK', 'DARI', 'KE', 'YANG'
        ];

        // Split nama produk menjadi kata-kata
        $words = preg_split('/\s+/', $productName);
        
        // Filter kata-kata: minimal 2 karakter, bukan common words, dan bukan angka saja
        $importantWords = array_filter($words, function($word) use ($commonWords) {
            $cleanWord = trim($word);
            return strlen($cleanWord) > 2 && 
                !in_array($cleanWord, $commonWords) &&
                !is_numeric($cleanWord) &&
                !preg_match('/^\d+[A-Z]*$/', $cleanWord); // Exclude things like "16GB", "250GR"
        });

        return array_values($importantWords);
    }

    // 📌 Fungsi untuk generate SKU otomatis
    private function generateSku($storeName)
    {
        // Ambil inisial dari nama toko
        $words = explode(' ', $storeName);
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty(trim($word))) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        // Cari produk terakhir dengan prefix yang sama
        $lastProduct = Product::where('sku', 'like', "SKU{$initials}%")
            ->orderBy('sku', 'desc')
            ->first();
        
        if ($lastProduct) {
            // Extract number dari SKU terakhir dan increment
            $lastNumber = intval(substr($lastProduct->sku, strlen("SKU{$initials}")));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: SKUCNG0001, SKUCNC0001, dll
        return "SKU{$initials}" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }


    // 📌 Fungsi untuk membersihkan nama produk dari kata umum
    private function cleanProductName($productName)
    {
        // Kata-kata umum yang sering ada di nama produk tapi tidak penting untuk matching
        $commonWords = [
            'SSD', 'HDD', 'USB', 'RAM', 'PROCESSOR', 'VGA', 'MONITOR', 'KEYBOARD', 'MOUSE',
            'LAPTOP', 'PC', 'KOMPUTER', 'HP', 'SMARTPHONE', 'TABLET',
            'GB', 'MB', 'TB', 'GHZ', 'MHZ', 'RPM',
            'SATA', 'NVME', 'M2', 'PCI', 'PCI-E',
            'WIFI', 'BLUETOOTH', 'LAN', 'HDMI', 'USB', 'VGA', 'DISPLAYPORT',
            'WARNA', 'COLOR', 'SIZE', 'UKURAN', 'TYPE', 'TIPE', 'MODEL',
            'BARU', 'NEW', 'ORIGINAL', 'ORI', 'GENUINE'
        ];

        // Split nama produk menjadi kata-kata
        $words = preg_split('/\s+/', $productName);
        
        // Filter kata-kata: minimal 2 karakter dan bukan common words
        $importantWords = array_filter($words, function($word) use ($commonWords) {
            return strlen($word) > 2 && 
                !in_array($word, $commonWords) &&
                !is_numeric($word);
        });

        return array_values($importantWords);
    }

    // 📌 Form edit produk
    public function edit(Product $product)
    {
        $categories = Category::all();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    // 📌 Update produk
    public function update(Request $request, Product $product)
    {

        $request->validate([
            'sku'         => 'required|unique:products,sku,' . $product->id,
            'name'        => 'required',
            'cost'        => 'required|numeric',
            'price'       => 'required|numeric',
            'discount'    => 'nullable|numeric|min:0|max:100',
            'category_id' => 'required|exists:categories,id', // tambahkan validasi kategori
        ]);

        $store = auth()->user()->stores()->with(['users.roles'])->first();

        if (!$store) {
            return redirect()->route('products.index')
                ->with('error', 'Anda belum memiliki toko, buat toko terlebih dahulu sebelum memperbarui produk.');
        }

        $product->update([
            'sku'         => $request->sku,
            'name'        => $request->name,
            'cost'        => $request->cost,
            'price'       => $request->price,
            'discount'    => $request->discount ?? 0,
            'store_id'    => $store->id,
            'category_id' => $request->category_id, // update kategori
            'updated_by'  => auth()->id(),
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui');
        
    }


    // 📌 Hapus produk
    public function destroy(Product $product)
    {
        // Ambil semua user toko
        $storeUsers = $product->store->users;

        // Cek apakah user login termasuk di store ini
        if (!$storeUsers->contains(auth()->id())) {
            abort(403, 'Unauthorized');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }


    // ProductController.php
    public function bulkDelete(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'Data tidak valid'], 422);
        }

        Product::whereIn('id', $ids)->delete();
        return response()->json(['message' => 'Produk berhasil dihapus']);
    }

}
