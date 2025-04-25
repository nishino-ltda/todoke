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
            $table->foreignId('clienteId')->constrained('users');
            $table->foreignId('restauranteId')->constrained('users');
            $table->enum('status', [
                'em_analise',
                'aceito', 
                'em_preparo',
                'aguardando_entregador',
                'entregador_retirou',
                'entregue',
                'cancelado'
            ])->default('em_analise');
            $table->decimal('valorTotal', 10, 2);
            $table->foreignId('entregaId')->nullable()->constrained('deliveries');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['restauranteId', 'status']);
            $table->index(['clienteId', 'created_at']);
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
