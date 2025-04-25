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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entregaId')->constrained('deliveries');
            $table->foreignId('avaliadorId')->constrained('users');
            $table->foreignId('avaliadoId')->constrained('users');
            $table->tinyInteger('nota')->unsigned();
            $table->text('comentario')->nullable();
            $table->timestamps();

            $table->index('entregaId');
            $table->index(['avaliadorId', 'avaliadoId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
