<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_pc_assemblies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pc_assemblies', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama rakitan
            $table->text('description')->nullable(); // Deskripsi rakitan
            $table->decimal('total_price', 12, 2); // Total harga
            $table->json('components'); // Komponen dalam format JSON
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pc_assemblies');
    }
};