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
            'courrier_id' => User::factory()->create(['type' => 'courrier'])->id,
            'origem' => [
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'endereco' => fake()->address()
            ],
            'destino' => [
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'endereco' => fake()->address()
            ],
            'status' => fake()->randomElement(['pending', 'accepted', 'in_transit', 'delivered', 'canceled']),
            'type' => fake()->randomElement(['normal', 'expressa', 'sustentavel']),
            'item_description' => fake()->sentence(),
            'estimated_weight' => fake()->randomFloat(2, 0.1, 10),
            'dimensions' => [
                'altura' => fake()->randomFloat(2, 5, 50),
                'largura' => fake()->randomFloat(2, 5, 50),
                'profundidade' => fake()->randomFloat(2, 5, 50)
            ],
            'value' => fake()->randomFloat(2, 5, 50),
            'estimated_time' => fake()->numberBetween(10, 120),
            'confirmation_code' => fake()->bothify('????-####'),
            'node_id' => Node::factory()->create()->id
        ];
    }
}
