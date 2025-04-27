<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Node>
 */
class NodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_id' => User::factory()->create(['type' => 'partner'])->id,
            'type' => fake()->randomElement(['partner', 'distribution_center', 'delivery_point']),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance', 'pending_approval']),
            'identifier' => 'NODE-' . fake()->unique()->numberBetween(1000, 9999),
            'capacity' => fake()->randomFloat(2, 10, 100),
            'region_id' => Region::factory()->create()->id,
            'current_position' => [
                'lat' => fake()->latitude(),
                'lng' => fake()->longitude()
            ]
        ];
    }
}
