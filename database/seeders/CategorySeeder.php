<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Laptop',
            'Desktop PC', 
            'Monitor',
            'Keyboard',
            'Mouse',
            'Headset',
            'Speaker',
            'Printer',
            'Scanner',
            'Webcam',
            'Motherboard',
            'Processor',
            'RAM',
            'Hard Disk',
            'SSD',
            'Power Supply',
            'VGA Card',
            'Casing',
            'CPU Fan',
            'Flashdisk',
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}