<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Addon>
 */
class AddonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 1, 10),
            'status' => 'available',
            'partner_id' => User::factory()->create(['type' => 'partner'])->id
        ];
    }
    
    /**
     * Configure the model to use a specific partner.
     *
     * @param int $partnerId
     * @return $this
     */
    public function forPartner($partnerId)
    {
        return $this->state(function (array $attributes) use ($partnerId) {
            return [
                'partner_id' => $partnerId,
            ];
        });
    }
    
    /**
     * Configure the model to be unavailable.
     *
     * @return $this
     */
    public function unavailable()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'unavailable',
            ];
        });
    }
}
