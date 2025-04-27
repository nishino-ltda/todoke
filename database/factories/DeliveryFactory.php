<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
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
            'courier_id' => User::factory()->create(['type' => 'courier'])->id,
            'logistics_partner_id' => User::factory()->create(['type' => 'partner'])->id,
            'origin' => [
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
                'address' => fake()->address()
            ],
            'destination' => [
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude(),
                'address' => fake()->address()
            ],
            'status' => fake()->randomElement(['pending', 'accepted', 'in_transit', 'delivered', 'canceled']),
            'type' => fake()->randomElement(['standard', 'express', 'priority']),
            'item_description' => fake()->sentence(),
            'estimated_weight' => fake()->randomFloat(2, 0.1, 10),
            'dimensions' => [
                'height' => fake()->randomFloat(2, 5, 50),
                'width' => fake()->randomFloat(2, 5, 50),
                'depth' => fake()->randomFloat(2, 5, 50)
            ],
            'value' => fake()->randomFloat(2, 5, 50),
            'estimated_time' => fake()->numberBetween(10, 120),
            'confirmation_code' => fake()->bothify('????-####'),
            'node_id' => Node::factory()->create()->id,
            'special_instructions' => fake()->boolean(70) ? fake()->sentence() : null,
            'payment_method' => fake()->randomElement([
                'credit_card',
                'debit_card',
                'pix',
                'cash',
                'voucher'
            ])
        ];
    }
}
