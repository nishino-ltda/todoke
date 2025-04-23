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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('restauranteId')->constrained('users');
            $table->string('nome');
            $table->text('descricao');
            $table->decimal('preco', 10, 2);
            $table->string('categoria');
            $table->string('imagemUrl')->nullable();
            $table->enum('status', ['disponivel', 'indisponivel'])->default('disponivel');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['restauranteId', 'status']);
            $table->index('categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
