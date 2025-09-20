<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil user admin (atau user pertama) untuk assign toko
        $userId = DB::table('users')->where('email', 'admin@example.com')->value('id');

        if (!$userId) {
            $userId = DB::table('users')->insertGetId([
                'name' => 'Default Owner',
                'email' => 'owner@example.com',
                'password' => bcrypt('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('stores')->insert([
            [
                'name' => 'cipta nugraha garut',
                'address' => 'jl terusan pahlawan sukagalih',
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'cipta nugraha cikajang',
                'address' => 'jl cibodas cikajang',
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
