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
        Schema::create('product_addon', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('addon_id')->constrained('addons');
            $table->primary(['product_id', 'addon_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_addon');
    }
};
