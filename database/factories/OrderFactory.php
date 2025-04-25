<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Delivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clientId' => User::factory()->create(['tipo' => 'cliente'])->id,
            'restaurantId' => User::factory()->create(['tipo' => 'parceiro'])->id,
            'status' => fake()->randomElement(['pending', 'accepted', 'preparing', 'awaiting_delivery', 'delivery_picked_up', 'delivered', 'canceled']),
            'totalValue' => fake()->randomFloat(2, 10, 200),
            'deliveryId' => Delivery::factory()->create()->id,
        ];
    }
}
