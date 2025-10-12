<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
        $categories = Category::orderBy('name')->get();
        $store = auth()->user()->stores()->first();
        
        // Generate SKU otomatis dengan kode toko
        $autoSku = $this->generateAutoSku($store);

        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'autoSku' => $autoSku,
        ]);
    }

    // 📌 Simpan produk baru (dengan pengecekan ketat)
    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|unique:products,sku',
            'name' => 'required|string|max:255',
            'initial_stock' => 'required|integer|min:0',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'category_id' => 'required|exists:categories,id',
        ]);

        try {
            DB::beginTransaction();

            $store = auth()->user()->stores()->first();
            
            if (!$store) {
                return back()->with('error', 'Anda belum memiliki toko.');
            }

            // Cek apakah produk dengan nama yang sama sudah ada
            $existingProduct = Product::where('name', $request->name)
                ->where('store_id', $store->id)
                ->first();

            if ($existingProduct) {
                return back()->with('error', 'Produk dengan nama yang sama sudah ada di toko ini.');
            }

            // Buat produk baru
            $product = Product::create([
                'sku' => $request->sku,
                'name' => $request->name,
                'cost' => $request->cost,
                'price' => $request->price,
                'discount' => $request->discount ?? 0,
                'category_id' => $request->category_id,
                'store_id' => $store->id,
                'created_by' => auth()->id(),
            ]);

            // ✅ BUAT STOK AWAL JIKA DIISI
            if ($request->initial_stock > 0) {
                Stock::create([
                    'product_id' => $product->id,
                    'type' => 'adjustment',
                    'quantity' => $request->initial_stock,
                    'user_id' => auth()->id(),
                    'reference' => 'product_creation',
                    'note' => 'Stok awal',
                ]);
            }

            DB::commit();

            // Generate SKU baru untuk form berikutnya
            $newSku = $this->generateAutoSku($store);

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil ditambahkan!')
                ->with('newSku', $newSku);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Generate kode SKU otomatis berdasarkan nama toko
     */
    private function generateAutoSku($store)
    {
        if (!$store) {
            return 'SKUDEF001'; // Default jika tidak ada toko
        }

        // Ambil kode toko dari nama toko
        $storeCode = $this->generateStoreCode($store->name);
        
        // Cari produk terakhir dengan kode toko yang sama
        $lastProduct = Product::where('sku', 'like', "SKU{$storeCode}%")
            ->orderBy('id', 'desc')
            ->first();
            
        // Generate nomor urut berikutnya
        if ($lastProduct) {
            // Extract number from existing SKU (format: SKUCNG001 -> 001)
            $lastNumber = (int) Str::substr($lastProduct->sku, 6); // 6 = length of "SKUCNG"
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        // Format: SKU + Kode Toko + Nomor Urut (3 digit)
        return "SKU{$storeCode}" . Str::padLeft($nextNumber, 3, '0');
    }

    /**
     * Generate kode toko dari nama toko
     * Contoh: 
     * - "cipta nugraha garut" -> "CNG"
     * - "cipta nugraha cikajang" -> "CNC"
     * - "toko abadi" -> "TOA"
     * - "supermarket" -> "SUP"
     */
    private function generateStoreCode($storeName)
    {
        // Bersihkan nama toko
        $cleanName = Str::lower(trim($storeName));
        
        // Hapus kata yang tidak penting (opsional)
        $unimportantWords = ['toko', 'store', 'shop', 'pt', 'cv', 'ud'];
        $words = array_filter(explode(' ', $cleanName), function($word) use ($unimportantWords) {
            return !in_array($word, $unimportantWords) && !empty($word);
        });
        
        $words = array_values($words); // Reset array keys
        
        $wordCount = count($words);
        
        if ($wordCount === 0) {
            return 'DEF'; // Default
        }
        
        if ($wordCount === 1) {
            // Satu kata: ambil 3 huruf pertama
            return Str::upper(Str::substr($words[0], 0, 3));
        }
        
        if ($wordCount === 2) {
            // Dua kata: 2 huruf dari kata pertama + 1 huruf dari kata kedua
            return Str::upper(
                Str::substr($words[0], 0, 2) . 
                Str::substr($words[1], 0, 1)
            );
        }
        
        // Tiga kata atau lebih: ambil inisial masing-masing kata (maks 3)
        $initials = '';
        foreach ($words as $word) {
            if (strlen($initials) < 3) {
                $initials .= $word[0];
            }
        }
        
        return Str::upper($initials);
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
            'category_id' => 'required|exists:categories,id',
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
            'category_id' => $request->category_id,
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