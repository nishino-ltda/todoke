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
            'partner_id' => User::factory()->create(['type' => 'partner'])->id,
            'name' => fake()->city() . ' Region',
            'polygon' => [
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
            'status' => fake()->randomElement(['active', 'inactive'])
        ];
    }
}
