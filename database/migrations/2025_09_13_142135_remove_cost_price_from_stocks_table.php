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
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn(['cost', 'price']);
        });
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('cost', 12, 2)->nullable();
            $table->decimal('price', 12, 2)->nullable();
        });
    }
};
