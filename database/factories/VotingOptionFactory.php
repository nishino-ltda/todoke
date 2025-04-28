<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\VotingRound;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VotingOption>
 */
class VotingOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'voting_round_id' => VotingRound::factory(),
            'min_fare_per_km' => $this->faker->randomFloat(2, 1, 5),
            'avg_fare_per_km' => $this->faker->randomFloat(2, 5, 10),
            'max_fare_per_km' => $this->faker->randomFloat(2, 10, 15),
        ];
    }
}
