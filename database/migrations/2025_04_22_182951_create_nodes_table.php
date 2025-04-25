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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parceiroId')->constrained('users');
            $table->enum('tipo', ['restaurante', 'centro_distribuicao', 'ponto_entrega']);
            $table->string('identificador')->unique();
            $table->decimal('capacidade', 10, 2)->nullable();
            $table->enum('status', ['ativo', 'inativo', 'manutencao'])->default('ativo');
            $table->foreignId('regiaoId')->constrained('regions');
            $table->json('posicaoAtual');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['regiaoId', 'tipo']);
            $table->index('parceiroId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
