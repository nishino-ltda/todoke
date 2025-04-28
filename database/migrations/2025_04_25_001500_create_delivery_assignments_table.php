<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('delivery_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('deliveries');
            $table->foreignId('partner_id')->constrained('users');
            $table->integer('stage');
            $table->enum('status', [
                'pending',
                'accepted',
                'collected',
                'in_transit',
                'delivered',
                'canceled',
                'drone_launched',
                'drone_in_route',
                'drone_arrived',
                'drone_returned'
            ])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_assignments');
    }
};
