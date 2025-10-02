<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
             $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment']);
            $table->integer('quantity');
            $table->string('reference')->nullable(); // kode transaksi / faktur
            $table->string('note')->nullable();
            

            // 🔥 tambahan untuk memisahkan sumber stok
            $table->string('source_type')->nullable(); // contoh: 'purchase', 'loan', 'return'
            $table->unsignedBigInteger('source_id')->nullable(); // id referensi dari tabel terkait

            $table->timestamps();

            // index biar query laporan lebih cepat
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
