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
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 14)->nullable()->unique();
            $table->string('license_number', 50)->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('license_file_path')->nullable();
            $table->string('business_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('business_document_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cpf',
                'license_number',
                'vehicle_type',
                'license_file_path',
                'business_name',
                'business_type',
                'tax_id',
                'address',
                'business_document_path'
            ]);
        });
    }
};
