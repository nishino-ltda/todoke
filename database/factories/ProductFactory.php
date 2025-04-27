<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'partner_id' => null,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 1, 100),
            'category' => fake()->word(),
            'imageUrl' => fake()->imageUrl(),
            'status' => fake()->randomElement(['available', 'unavailable']),
        ];
    }

    /**
     * Set the partner ID for the product.
     *
     * @param  string  $partnerId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forPartner($partnerId)
    {
        return $this->state(function (array $attributes) use ($partnerId) {
            return [
                'partner_id' => $partnerId
            ];
        });
    }
}
