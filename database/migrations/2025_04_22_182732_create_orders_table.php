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
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('partner_id')->constrained('users');
            $table->enum('status', [
                'pending',
                'accepted', 
                'preparing',
                'awaiting_delivery',
                'delivery_picked_up',
                'delivered',
                'canceled'
            ])->default('pending');
            $table->decimal('total_value', 10, 2);
            $table->foreignId('delivery_id')->nullable()->constrained('deliveries');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['partner_id', 'status']);
            $table->index(['customer_id', 'created_at']);
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
