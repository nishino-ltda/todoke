<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parceiroId' => User::factory()->create(['tipo' => 'parceiro'])->id,
            'name' => fake()->city() . ' Region',
            'poligono' => [
                [
                    'latitude' => fake()->latitude(),
                    'longitude' => fake()->longitude()
                ],
                [
                    'latitude' => fake()->latitude(),
                    'longitude' => fake()->longitude()
                ],
                [
                    'latitude' => fake()->latitude(),
                    'longitude' => fake()->longitude()
                ]
            ],
            'status' => fake()->randomElement(['ativo', 'inativo'])
        ];
    }
}
