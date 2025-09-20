<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $storeId = DB::table('stores')->value('id'); // toko pertama
        if (!$storeId) {
            $storeId = DB::table('stores')->insertGetId([
                'name' => 'Default Store',
                'address' => 'Jl. Default',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ambil semua kategori
        $categories = DB::table('categories')->pluck('id', 'name');

        $products = [
            ['Asus Vivobook 14', 8500000, 7500000, 'Laptop'],
            ['Lenovo Ideapad Slim 3', 7200000, 6500000, 'Laptop'],
            ['HP Pavilion Gaming 15', 13500000, 12000000, 'Laptop'],
            ['Acer Aspire 5', 7500000, 6800000, 'Laptop'],
            ['Dell Inspiron 14', 9200000, 8500000, 'Laptop'],

            ['Intel Core i5-12400F', 2800000, 2500000, 'Processor'],
            ['Intel Core i7-13700K', 6200000, 5800000, 'Processor'],
            ['AMD Ryzen 5 5600', 2300000, 2000000, 'Processor'],
            ['AMD Ryzen 7 5800X3D', 4500000, 4200000, 'Processor'],
            ['AMD Ryzen 9 7900X', 7200000, 6800000, 'Processor'],

            ['ASUS Prime B660M-K D4', 2200000, 2000000, 'Motherboard'],
            ['MSI B550M Pro-VDH WiFi', 2300000, 2100000, 'Motherboard'],
            ['Gigabyte A520M S2H', 1300000, 1150000, 'Motherboard'],
            ['ASRock B760M Pro RS', 2400000, 2200000, 'Motherboard'],
            ['ASUS ROG Strix X670E-F', 7200000, 6800000, 'Motherboard'],

            ['Corsair Vengeance 16GB DDR4', 950000, 800000, 'RAM'],
            ['Kingston Fury Beast 8GB DDR4', 480000, 400000, 'RAM'],
            ['Team T-Force Delta 32GB DDR5', 2200000, 2000000, 'RAM'],
            ['G.Skill Trident Z 16GB DDR5', 2100000, 1900000, 'RAM'],

            ['Seagate Barracuda 1TB HDD', 550000, 450000, 'Hard Disk'],
            ['WD Blue 2TB HDD', 850000, 750000, 'Hard Disk'],
            ['Samsung 870 EVO 500GB SSD', 950000, 850000, 'SSD'],
            ['Crucial MX500 1TB SSD', 1600000, 1450000, 'SSD'],
            ['Kingston NV2 1TB NVMe SSD', 1150000, 1000000, 'SSD'],

            ['NVIDIA GTX 1660 Super', 3800000, 3500000, 'VGA Card'],
            ['NVIDIA RTX 3060 Ti', 5200000, 4800000, 'VGA Card'],
            ['NVIDIA RTX 4070 Super', 9500000, 9000000, 'VGA Card'],
            ['AMD Radeon RX 6600', 3400000, 3100000, 'VGA Card'],
            ['AMD Radeon RX 7800 XT', 8500000, 8000000, 'VGA Card'],

            ['Corsair RM750 750W PSU', 1600000, 1400000, 'Power Supply'],
            ['Seasonic S12III 650W PSU', 950000, 800000, 'Power Supply'],
            ['Cooler Master MWE 550W PSU', 750000, 650000, 'Power Supply'],

            ['Logitech G102 Mouse', 220000, 180000, 'Mouse'],
            ['Razer DeathAdder Essential', 350000, 300000, 'Mouse'],
            ['SteelSeries Rival 3', 420000, 360000, 'Mouse'],

            ['Logitech K120 Keyboard', 120000, 100000, 'Keyboard'],
            ['Razer Cynosa V2 Keyboard', 750000, 680000, 'Keyboard'],
            ['Keychron K2 Wireless Keyboard', 1200000, 1100000, 'Keyboard'],

            ['Samsung Odyssey G5 27" Monitor', 3200000, 2900000, 'Monitor'],
            ['LG UltraGear 24" Monitor', 2600000, 2400000, 'Monitor'],
            ['AOC 24G2 24" Monitor', 2800000, 2500000, 'Monitor'],

            ['Logitech Z313 Speaker', 520000, 450000, 'Speaker'],
            ['Edifier R980T Speaker', 950000, 850000, 'Speaker'],
            ['Microlab M-108 Speaker', 350000, 300000, 'Speaker'],

            ['HP DeskJet 2336 Printer', 750000, 650000, 'Printer'],
            ['Canon PIXMA G2020 Printer', 2500000, 2300000, 'Printer'],
            ['Epson L3150 Printer', 2800000, 2600000, 'Printer'],
        ];

        $counter = 1;
        foreach ($products as [$name, $price, $cost, $catName]) {
            DB::table('products')->insert([
                'name'        => $name,
                'sku'         => 'SKU' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                'price'       => $price,
                'cost'        => $cost,
                'discount'    => 0,
                'store_id'    => $storeId,
                'category_id' => $categories[$catName] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $counter++;
        }
    }
}
