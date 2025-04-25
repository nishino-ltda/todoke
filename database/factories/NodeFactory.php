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
            'type' => fake()->randomElement(['restaurante', 'centro_distribuicao', 'ponto_entrega']),
            'identificador' => fake()->bothify('NODE-####'),
            'capacity' => fake()->randomFloat(2, 10, 100),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
            'region_id' => Region::factory()->create()->id,
            'current_position' => [
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'endereco' => fake()->address()
            ]
        ];
    }
}
