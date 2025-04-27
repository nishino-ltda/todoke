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
            $table->foreignId('courier_id')->nullable()->constrained('users');
            $table->foreignId('logistics_partner_id')
                ->nullable()
                ->constrained('users')
                ->comment('Parceiro logístico responsável pela entrega');

            $table->json('current_position')->nullable();
            $table->json('status_history')->nullable();
            $table->json('origin');
            $table->json('destination');
            $table->enum('status', [
                'pending',
                'accepted',
                'in_transit',
                'collected',
                'delivered',
                'canceled'
            ])->default('pending');
            $table->enum('type', ['standard', 'express', 'sustainable', 'priority']);
            $table->string('item_description');
            $table->decimal('estimated_weight', 10, 2)->nullable();
            $table->json('dimensions')->nullable();
            $table->decimal('value', 10, 2);
            $table->integer('estimated_time')->nullable();
            $table->string('confirmation_code')->nullable();
            $table->json('stages')->nullable();
            $table->foreignId('node_id')->nullable()->constrained('nodes');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index(['courier_id', 'status']);
            $table->index('logistics_partner_id');
            $table->index('node_id');

            $table->text('special_instructions')->nullable()
                ->comment('Instruções especiais para a entrega');
            $table->enum('payment_method', [
                'credit_card',
                'debit_card',
                'pix',
                'cash',
                'voucher'
            ])->nullable()
                ->comment('Método de pagamento');
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
