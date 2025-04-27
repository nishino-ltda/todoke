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
            $table->foreignId('partner_id')->constrained('users');
            $table->enum('type', ['partner', 'distribution_center', 'delivery_point']);
            $table->string('identifier')->unique();
            $table->decimal('capacity', 10, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'pending_approval'])->default('pending_approval');
            $table->foreignId('region_id')->constrained('regions');
            $table->json('current_position');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['region_id', 'type']);
            $table->index('partner_id');
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
