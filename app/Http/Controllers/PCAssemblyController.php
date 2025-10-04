<?php
// app/Http/Controllers/PCAssemblyController.php

namespace App\Http\Controllers;

use App\Models\PCAssembly;
use App\Models\Product;
use App\Models\Category;
use App\Models\Stock;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PCAssemblyController extends Controller
{
    // Halaman simulasi rakitan
    public function create()
    {
        // ✅ Mapping dari kategori database ke format yang diharapkan frontend AI
        $categoryMapping = [
            'Processor' => 'processors',
            'Motherboard' => 'motherboards', 
            'RAM' => 'memories',
            'VGA Card' => 'graphics_cards',
            'SSD' => 'storages',
            'Hard Disk' => 'storages', // Gabung dengan SSD
            'Power Supply' => 'power_supplies',
            'Casing' => 'cases',
            'CPU Fan' => 'cpu_coolers'
        ];

        $components = Product::with(['category', 'stocks'])
            ->whereHas('category', function($query) use ($categoryMapping) {
                $query->whereIn('name', array_keys($categoryMapping));
            })
            ->whereHas('stocks', function($query) {
                $query->where('quantity', '>', 0);
            })
            ->get();

        // ✅ Group by mapped categories untuk frontend AI
        $mappedComponents = [];
        foreach ($components as $product) {
            $originalCategory = $product->category->name;
            $mappedCategory = $categoryMapping[$originalCategory] ?? null;
            
            if ($mappedCategory) {
                if (!isset($mappedComponents[$mappedCategory])) {
                    $mappedComponents[$mappedCategory] = [];
                }
                $mappedComponents[$mappedCategory][] = $product;
            }
        }

        // Debug: Lihat struktur akhir
        logger('Mapped PC Components for AI:', [
            'original_categories' => $components->pluck('category.name')->unique()->toArray(),
            'mapped_categories' => array_keys($mappedComponents),
            'products_per_mapped_category' => array_map('count', $mappedComponents)
        ]);

        return Inertia::render('PCAssembly/Create', [
            'components' => $mappedComponents, // ✅ Kirim yang sudah di-mapping
            'aiTemplates' => $this->getAITemplates(),
        ]);
    }

    // ✅ PERBAIKAN: AI Templates dengan format yang diharapkan frontend
    private function getAITemplates()
    {
        return [
            'gaming' => [
                'name' => '🎮 Gaming PC',
                'description' => 'Optimized for gaming performance',
                'priority' => ['graphics_cards', 'processors', 'motherboards', 'memories'],
                'budgetAllocation' => [
                    'graphics_cards' => 0.35,
                    'processors' => 0.25,
                    'motherboards' => 0.15,
                    'memories' => 0.10,
                    'storages' => 0.08,
                    'power_supplies' => 0.05,
                    'cases' => 0.02
                ]
            ],
            'office' => [
                'name' => '💼 Office PC', 
                'description' => 'Optimal untuk produktivitas kerja',
                'priority' => ['processors', 'memories', 'storages', 'motherboards'],
                'budgetAllocation' => [
                    'processors' => 0.30,
                    'memories' => 0.20,
                    'storages' => 0.15,
                    'motherboards' => 0.15,
                    'power_supplies' => 0.10,
                    'cases' => 0.08,
                    'graphics_cards' => 0.02
                ]
            ],
            'editing' => [
                'name' => '🎬 Content Creation',
                'description' => 'Didesain untuk video/photo editing',
                'priority' => ['processors', 'memories', 'graphics_cards', 'storages'],
                'budgetAllocation' => [
                    'processors' => 0.28,
                    'memories' => 0.22,
                    'graphics_cards' => 0.20,
                    'storages' => 0.12,
                    'motherboards' => 0.10,
                    'power_supplies' => 0.06,
                    'cases' => 0.02
                ]
            ],
            'budget' => [
                'name' => '💰 Budget Build',
                'description' => 'Maksimalkan performa dengan budget terbatas',
                'priority' => ['processors', 'motherboards', 'memories', 'storages'],
                'budgetAllocation' => [
                    'processors' => 0.25,
                    'motherboards' => 0.20,
                    'memories' => 0.18,
                    'storages' => 0.15,
                    'power_supplies' => 0.12,
                    'cases' => 0.08,
                    'graphics_cards' => 0.02
                ]
            ]
        ];
    }

    // ... method store, history, show tetap sama

    // Simpan rakitan ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'components' => 'required|array',
            'total_price' => 'required|numeric|min:0',
        ]);

        // Cek ketersediaan stok untuk semua komponen
        foreach ($request->components as $component) {
            $product = Product::find($component['id']);
            $totalStock = $product->stocks->sum('quantity');
            
            if ($totalStock <= 0) {
                return back()->withErrors([
                    'components' => "Produk {$product->name} stok habis."
                ]);
            }
        }

        $assembly = PCAssembly::create([
            'name' => $request->name,
            'description' => $request->description,
            'components' => $request->components,
            'total_price' => $request->total_price,
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->stores->first()->id,
        ]);

        return redirect()->route('pc-assembly.history')
            ->with('success', 'Rakitan PC berhasil disimpan!');
    }

    // History rakitan
    public function history()
    {
        $assemblies = PCAssembly::with(['user', 'store'])
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return Inertia::render('PCAssembly/History', [
            'assemblies' => $assemblies,
        ]);
    }

    // Detail rakitan
    public function show(PCAssembly $assembly)
    {
        $assembly->load([
            'user' => function($query) {
                $query->select('id', 'name', 'email');
            },
            'store' => function($query) {
                $query->select('id', 'name');
            }
        ]);

        // Authorization - hanya pembuat yang bisa lihat
        if ($assembly->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('PCAssembly/Show', [
            'assembly' => $assembly,
        ]);
    }
}