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
            // LAPTOP (6 produk)
            ['Asus Vivobook 14 A1404ZA', 8500000, 7500000, 'Laptop'],
            ['Lenovo Ideapad Slim 3 15IAH8', 7200000, 6500000, 'Laptop'],
            ['HP Pavilion Gaming 15-ec2013AX', 13500000, 12000000, 'Laptop'],
            ['Acer Aspire 5 A515-57G', 7500000, 6800000, 'Laptop'],
            ['Dell Inspiron 14 5420', 9200000, 8500000, 'Laptop'],
            ['MSI Modern 14 C13M', 8900000, 8000000, 'Laptop'],

            // PROCESSOR (6 produk)
            ['Intel Core i5-12400F', 2800000, 2500000, 'Processor'],
            ['Intel Core i7-13700K', 6200000, 5800000, 'Processor'],
            ['AMD Ryzen 5 5600', 2300000, 2000000, 'Processor'],
            ['AMD Ryzen 7 5800X3D', 4500000, 4200000, 'Processor'],
            ['AMD Ryzen 9 7900X', 7200000, 6800000, 'Processor'],
            ['Intel Core i3-12100F', 1800000, 1600000, 'Processor'],

            // MOTHERBOARD (6 produk)
            ['ASUS Prime B660M-K D4', 2200000, 2000000, 'Motherboard'],
            ['MSI B550M Pro-VDH WiFi', 2300000, 2100000, 'Motherboard'],
            ['Gigabyte A520M S2H', 1300000, 1150000, 'Motherboard'],
            ['ASRock B760M Pro RS', 2400000, 2200000, 'Motherboard'],
            ['ASUS ROG Strix X670E-F', 7200000, 6800000, 'Motherboard'],
            ['Gigabyte B760 Gaming X', 2600000, 2400000, 'Motherboard'],

            // RAM (6 produk)
            ['Corsair Vengeance LPX 16GB DDR4 3200MHz', 950000, 800000, 'RAM'],
            ['Kingston Fury Beast 8GB DDR4 3200MHz', 480000, 400000, 'RAM'],
            ['Team T-Force Delta RGB 32GB DDR5 5600MHz', 2200000, 2000000, 'RAM'],
            ['G.Skill Trident Z Neo 16GB DDR4 3600MHz', 1200000, 1100000, 'RAM'],
            ['ADATA XPG Spectrix D50 16GB DDR4 3200MHz', 850000, 750000, 'RAM'],
            ['Crucial Ballistix 32GB DDR4 3200MHz', 1800000, 1600000, 'RAM'],

            // HARD DISK (6 produk)
            ['Seagate Barracuda 1TB 7200RPM HDD', 550000, 450000, 'Hard Disk'],
            ['WD Blue 2TB 7200RPM HDD', 850000, 750000, 'Hard Disk'],
            ['Toshiba P300 3TB 7200RPM HDD', 1200000, 1100000, 'Hard Disk'],
            ['Seagate IronWolf 4TB NAS HDD', 1850000, 1700000, 'Hard Disk'],
            ['WD Black 1TB Performance HDD', 750000, 650000, 'Hard Disk'],
            ['Seagate SkyHawk 2TB Surveillance HDD', 950000, 850000, 'Hard Disk'],

            // SSD (6 produk)
            ['Samsung 870 EVO 500GB SATA SSD', 950000, 850000, 'SSD'],
            ['Crucial MX500 1TB SATA SSD', 1600000, 1450000, 'SSD'],
            ['Kingston NV2 1TB NVMe PCIe 4.0 SSD', 1150000, 1000000, 'SSD'],
            ['WD Blue SN580 1TB NVMe SSD', 1250000, 1100000, 'SSD'],
            ['Samsung 980 Pro 1TB NVMe SSD', 1850000, 1700000, 'SSD'],
            ['Team Group MP34 2TB NVMe SSD', 2200000, 2000000, 'SSD'],

            // VGA CARD (6 produk)
            ['NVIDIA GTX 1660 Super 6GB', 3800000, 3500000, 'VGA Card'],
            ['NVIDIA RTX 3060 Ti 8GB', 5200000, 4800000, 'VGA Card'],
            ['NVIDIA RTX 4070 Super 12GB', 9500000, 9000000, 'VGA Card'],
            ['AMD Radeon RX 6600 8GB', 3400000, 3100000, 'VGA Card'],
            ['AMD Radeon RX 7800 XT 16GB', 8500000, 8000000, 'VGA Card'],
            ['NVIDIA RTX 4060 Ti 8GB', 6800000, 6300000, 'VGA Card'],

            // POWER SUPPLY (6 produk)
            ['Corsair RM750 750W 80+ Gold', 1600000, 1400000, 'Power Supply'],
            ['Seasonic S12III 650W 80+ Bronze', 950000, 800000, 'Power Supply'],
            ['Cooler Master MWE 550W 80+ Bronze', 750000, 650000, 'Power Supply'],
            ['be quiet! Pure Power 11 600W', 1200000, 1000000, 'Power Supply'],
            ['FSP Hydro G Pro 850W 80+ Gold', 1850000, 1600000, 'Power Supply'],
            ['Thermaltake Toughpower GF1 750W', 1500000, 1300000, 'Power Supply'],

            // CASING (6 produk)
            ['NZXT H510 Flow Mid Tower', 1200000, 1000000, 'Casing'],
            ['Corsair 4000D Airflow Mid Tower', 1500000, 1300000, 'Casing'],
            ['Lian Li Lancool 215 Mid Tower', 1400000, 1200000, 'Casing'],
            ['Fractal Design Pop Air RGB', 1300000, 1100000, 'Casing'],
            ['Phanteks Eclipse P360A', 1250000, 1050000, 'Casing'],
            ['Cooler Master MasterBox TD500', 1350000, 1150000, 'Casing'],

            // CPU FAN (6 produk)
            ['Cooler Master Hyper 212 RGB Black Edition', 650000, 550000, 'CPU Fan'],
            ['Deepcool Gammaxx 400 V2 Blue', 350000, 300000, 'CPU Fan'],
            ['Noctua NH-D15 Chromax Black', 1200000, 1000000, 'CPU Fan'],
            ['be quiet! Dark Rock Pro 4', 1100000, 950000, 'CPU Fan'],
            ['ID-Cooling SE-224-XTS Basic', 300000, 250000, 'CPU Fan'],
            ['Arctic Freezer 34 eSports DUO', 750000, 650000, 'CPU Fan'],

            // DESKTOP PC (6 produk)
            ['Dell OptiPlex 3090 Micro Desktop', 6500000, 5800000, 'Desktop PC'],
            ['HP ProDesk 600 G6 Desktop', 7200000, 6500000, 'Desktop PC'],
            ['Lenovo ThinkCentre M75s', 7800000, 7000000, 'Desktop PC'],
            ['Acer Veriton VX4670G Desktop', 8500000, 7500000, 'Desktop PC'],
            ['ASUS ExpertCenter D500SC', 9200000, 8200000, 'Desktop PC'],
            ['Intel NUC 13 Pro Mini PC', 9500000, 8500000, 'Desktop PC'],

            // MONITOR (6 produk)
            ['Samsung Odyssey G5 27" 144Hz', 3200000, 2900000, 'Monitor'],
            ['LG UltraGear 24" 144Hz IPS', 2600000, 2400000, 'Monitor'],
            ['AOC 24G2 24" 144Hz IPS', 2800000, 2500000, 'Monitor'],
            ['ASUS TUF Gaming VG249Q 24"', 2700000, 2400000, 'Monitor'],
            ['Dell S2721HGF 27" 144Hz', 3100000, 2800000, 'Monitor'],
            ['MSI G241 24" 144Hz IPS', 2500000, 2200000, 'Monitor'],

            // KEYBOARD (6 produk)
            ['Logitech K120 Keyboard USB', 120000, 100000, 'Keyboard'],
            ['Razer Cynosa V2 RGB Keyboard', 750000, 680000, 'Keyboard'],
            ['Keychron K2 Wireless Mechanical', 1200000, 1100000, 'Keyboard'],
            ['Royal Kludge RK61 Wireless', 650000, 580000, 'Keyboard'],
            ['Fantech MaxFit61 Mechanical', 550000, 480000, 'Keyboard'],
            ['Rexus Daxa M84 Pro Mechanical', 850000, 750000, 'Keyboard'],

            // MOUSE (6 produk)
            ['Logitech G102 Lightsync Mouse', 220000, 180000, 'Mouse'],
            ['Razer DeathAdder Essential', 350000, 300000, 'Mouse'],
            ['SteelSeries Rival 3 Gaming Mouse', 420000, 360000, 'Mouse'],
            ['Fantech X17 Thor RGB Mouse', 180000, 150000, 'Mouse'],
            ['Rexus X5 Sierra Wireless Mouse', 250000, 200000, 'Mouse'],
            ['Corsair Harpoon RGB Wireless', 450000, 380000, 'Mouse'],

            // HEADSET (6 produk)
            ['Logitech G332 Stereo Headset', 550000, 480000, 'Headset'],
            ['Razer Kraken X Lite Headset', 450000, 380000, 'Headset'],
            ['SteelSeries Arctis 1 Wireless', 850000, 750000, 'Headset'],
            ['HyperX Cloud Stinger Core', 600000, 520000, 'Headset'],
            ['Fantech HG15 Captain RGB', 350000, 300000, 'Headset'],
            ['Rexus Vonix F60 Gaming Headset', 480000, 400000, 'Headset'],

            // SPEAKER (6 produk)
            ['Logitech Z313 2.1 Speaker System', 520000, 450000, 'Speaker'],
            ['Edifier R980T 4" Bookshelf Speakers', 950000, 850000, 'Speaker'],
            ['Microlab M-108 2.0 Speakers', 350000, 300000, 'Speaker'],
            ['Creative Pebble V3 USB-C Speakers', 300000, 250000, 'Speaker'],
            ['JBL Go 3 Portable Speaker', 550000, 480000, 'Speaker'],
            ['Fantech SPK05 RGB Gaming Speakers', 280000, 230000, 'Speaker'],

            // PRINTER (6 produk)
            ['HP DeskJet 2336 All-in-One', 750000, 650000, 'Printer'],
            ['Canon PIXMA G2020 Tank Printer', 2500000, 2300000, 'Printer'],
            ['Epson L3150 EcoTank All-in-One', 2800000, 2600000, 'Printer'],
            ['Brother DCP-T420W Ink Tank', 2200000, 2000000, 'Printer'],
            ['HP LaserJet Pro M15w', 1800000, 1600000, 'Printer'],
            ['Canon iP2770 Inkjet Printer', 600000, 500000, 'Printer'],

            // SCANNER (6 produk)
            ['Canon CanoScan LiDE 300 Scanner', 1200000, 1000000, 'Scanner'],
            ['Epson Perfection V39 Scanner', 1500000, 1300000, 'Scanner'],
            ['Brother DS-640 Mobile Scanner', 2500000, 2200000, 'Scanner'],
            ['HP ScanJet Pro 2500 f1 Scanner', 2800000, 2500000, 'Scanner'],
            ['Fujitsu ScanSnap iX1500 Scanner', 4500000, 4000000, 'Scanner'],
            ['Plustek OpticBook 3800 Scanner', 3200000, 2900000, 'Scanner'],

            // WEBCAM (6 produk)
            ['Logitech C920 HD Pro Webcam', 850000, 750000, 'Webcam'],
            ['Razer Kiyo Streaming Webcam', 1200000, 1100000, 'Webcam'],
            ['Microsoft LifeCam HD-3000', 350000, 300000, 'Webcam'],
            ['AverMedia PW313 Webcam', 650000, 580000, 'Webcam'],
            ['Fantech iStyle WC01 Webcam', 280000, 230000, 'Webcam'],
            ['Rexus Camelia F1 Webcam 1080p', 450000, 380000, 'Webcam'],

            // FLASHDISK (6 produk)
            ['SanDisk Cruzer Blade 32GB USB 2.0', 80000, 60000, 'Flashdisk'],
            ['Kingston DataTraveler 64GB USB 3.0', 150000, 120000, 'Flashdisk'],
            ['Samsung BAR Plus 128GB USB 3.1', 350000, 300000, 'Flashdisk'],
            ['HP x765w 32GB USB 3.0 Flashdisk', 90000, 70000, 'Flashdisk'],
            ['Toshiba TransMemory 64GB USB 3.0', 130000, 100000, 'Flashdisk'],
            ['Lexar JumpDrive S75 128GB USB 3.0', 320000, 280000, 'Flashdisk'],
        ];

        $counter = 1;
        foreach ($products as [$name, $price, $cost, $catName]) {
            DB::table('products')->insert([
                'name'        => $name,
                'sku'         => 'SKU' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                'price'       => $price,
                'cost'        => $cost,
                'discount'    => rand(0, 15), // Tambahkan diskon random 0-15%
                'store_id'    => $storeId,
                'category_id' => $categories[$catName] ?? null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $counter++;
        }
    }
}