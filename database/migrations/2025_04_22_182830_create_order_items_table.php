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
        Schema::create('order_items', function (Blueprint $table) {
            $table->foreignUuid('order_id')->constrained('orders');
            $table->foreignUuid('product_id')->constrained('products');
            $table->integer('quantidade');
            $table->decimal('precoUnitario', 10, 2);
            $table->primary(['order_id', 'product_id']);
            $table->timestamps();

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
