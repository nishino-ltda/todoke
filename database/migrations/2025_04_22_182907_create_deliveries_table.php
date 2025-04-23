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
            $table->uuid('id')->primary();
            $table->foreignUuid('clienteId')->constrained('users');
            $table->foreignUuid('entregadorId')->nullable()->constrained('users');
            $table->json('origem');
            $table->json('destino');
            $table->enum('status', [
                'pendente',
                'aceito',
                'em_transporte',
                'entregue',
                'cancelado'
            ])->default('pendente');
            $table->enum('tipo', ['normal', 'expressa', 'sustentavel']);
            $table->string('descricaoItem');
            $table->decimal('pesoEstimado', 10, 2)->nullable();
            $table->json('dimensoes')->nullable();
            $table->decimal('valor', 10, 2);
            $table->integer('tempoEstimado')->nullable();
            $table->string('codigoConfirmacao')->nullable();
            $table->foreignUuid('nodeId')->nullable()->constrained('nodes');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clienteId', 'status']);
            $table->index(['entregadorId', 'status']);
            $table->index('nodeId');
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
