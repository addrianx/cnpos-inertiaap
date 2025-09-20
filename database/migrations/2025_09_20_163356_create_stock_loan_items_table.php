<?php

// database/migrations/xxxx_xx_xx_create_stock_loan_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stock_loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_loan_id')->constrained('stock_loans')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_loan_items');
    }
};
