<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Ubah product_id menjadi nullable
            $table->foreignId('product_id')->nullable()->change();
            
            // Tambah kolom untuk jasa
            $table->string('item_type')->default('product'); // product, service
            $table->string('service_name')->nullable(); // Nama jasa
            $table->text('service_description')->nullable(); // Deskripsi jasa (opsional)
            
            // Hapus constraint lama dan buat yang baru
            $table->dropForeign(['product_id']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['item_type', 'service_name', 'service_description']);
            $table->foreignId('product_id')->change();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
