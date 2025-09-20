<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'name' => 'admin',
                'label' => 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'manager',
                'label' => 'Manager',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'kasir',
                'label' => 'Kasir',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
