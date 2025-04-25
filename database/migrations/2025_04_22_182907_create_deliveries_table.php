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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('courrier_id')->nullable()->constrained('users');
            $table->json('current_position')->nullable();
            $table->json('status_history')->nullable();
            $table->json('origem');
            $table->json('destino');
            $table->enum('status', [
                'pending',
                'accepted',
                'in_transit',
                'delivered',
                'canceled'
            ])->default('pending');
            $table->enum('type', ['normal', 'expressa', 'sustentavel']);
            $table->string('item_description');
            $table->decimal('estimated_weight', 10, 2)->nullable();
            $table->json('dimensions')->nullable();
            $table->decimal('value', 10, 2);
            $table->integer('estimated_time')->nullable();
            $table->string('confirmation_code')->nullable();
            $table->foreignId('node_id')->nullable()->constrained('nodes');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['courrier_id', 'status']);
            $table->index('node_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
