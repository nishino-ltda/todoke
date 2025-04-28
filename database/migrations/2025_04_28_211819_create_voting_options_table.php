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
        Schema::create('voting_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_round_id')->constrained()->onDelete('cascade');
            $table->decimal('min_fare_per_km', 8, 2);
            $table->decimal('avg_fare_per_km', 8, 2);
            $table->decimal('max_fare_per_km', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_options');
    }
};
