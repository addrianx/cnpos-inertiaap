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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_code', 20)->unique()->after('id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // kasir
            $table->decimal('subtotal', 12, 2);    // sebelum diskon
            $table->decimal('discount', 12, 2)->default(0); // diskon transaksi (nominal)
            $table->decimal('total', 12, 2);       // setelah diskon
            $table->decimal('paid', 12, 2);
            $table->decimal('change', 12, 2)->default(0);
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
