<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delivery_id' => Delivery::factory()->create()->id,
            'rater_id' => User::factory()->create(['type' => 'customer'])->id,
            'rated_id' => User::factory()->create(['type' => 'courrier'])->id,
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->sentence()
        ];
    }
}
