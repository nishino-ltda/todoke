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
            'customer_id' => User::factory()->create(['type' => 'customer'])->id,
            'restaurant_id' => User::factory()->create(['type' => 'partner'])->id,
            'status' => fake()->randomElement(['pending', 'accepted', 'preparing', 'awaiting_delivery', 'delivery_picked_up', 'delivered', 'canceled']),
            'total_value' => fake()->randomFloat(2, 10, 200),
            'delivery_id' => Delivery::factory()->create()->id,
        ];
    }
}
