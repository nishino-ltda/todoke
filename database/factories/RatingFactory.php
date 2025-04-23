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
            'entregaId' => Delivery::factory()->create()->id,
            'avaliadorId' => User::factory()->create(['tipo' => 'cliente'])->id,
            'avaliadoId' => User::factory()->create(['tipo' => 'entregador'])->id,
            'nota' => fake()->numberBetween(1, 5),
            'comentario' => fake()->sentence()
        ];
    }
}
