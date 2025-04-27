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
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [
                        [
                            [fake()->longitude(), fake()->latitude()],
                            [fake()->longitude(), fake()->latitude()],
                            [fake()->longitude(), fake()->latitude()],
                            [fake()->longitude(), fake()->latitude()],
                            [fake()->longitude(), fake()->latitude()]
                        ]
                    ]
                ],
                'properties' => []
            ],
            'status' => fake()->randomElement(['active', 'inactive'])
        ];
    }
}
