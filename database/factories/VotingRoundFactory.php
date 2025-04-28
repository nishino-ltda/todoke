<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Region;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VotingRound>
 */
class VotingRoundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'start_time' => Carbon::now(),
            'end_time' => Carbon::now()->addMonth(),
            'status' => $this->faker->randomElement(['active', 'closed']),
            'region_id' => Region::factory(),
        ];
    }

    /**
     * Indicate that the voting round is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'start_time' => Carbon::now()->subDay(),
            'end_time' => Carbon::now()->addDay(),
        ]);
    }

    /**
     * Indicate that the voting round is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
            'start_time' => Carbon::now()->subMonth()->subDay(),
            'end_time' => Carbon::now()->subMonth(),
        ]);
    }
}
