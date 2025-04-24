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
            'restauranteId' => \App\Models\User::factory()->create(['tipo' => 'parceiro'])->id,
            'name' => fake()->words(3, true),
            'descricao' => fake()->sentence(),
            'preco' => fake()->randomFloat(2, 1, 100),
            'categoria' => fake()->word(),
            'imagemUrl' => fake()->imageUrl(),
            'status' => fake()->randomElement(['disponivel', 'indisponivel']),
        ];
    }
}
