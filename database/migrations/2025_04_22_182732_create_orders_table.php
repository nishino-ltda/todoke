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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clientId')->constrained('users');
            $table->foreignId('restaurantId')->constrained('users');
            $table->enum('status', [
                'pending',
                'accepted', 
                'preparing',
                'awaiting_delivery',
                'delivery_picked_up',
                'delivered',
                'canceled'
            ])->default('pending');
            $table->decimal('totalValue', 10, 2);
            $table->foreignId('deliveryId')->nullable()->constrained('deliveries');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['restaurantId', 'status']);
            $table->index(['clientId', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
