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
            'restauranteId' => null,
            'name' => fake()->words(3, true),
            'descricao' => fake()->sentence(),
            'preco' => fake()->randomFloat(2, 1, 100),
            'categoria' => fake()->word(),
            'imagemUrl' => fake()->imageUrl(),
            'status' => fake()->randomElement(['disponivel', 'indisponivel']),
        ];
    }

    /**
     * Set the restaurante ID for the product.
     *
     * @param  string  $restauranteId
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function forRestaurante($restauranteId)
    {
        return $this->state(function (array $attributes) use ($restauranteId) {
            return [
                'restauranteId' => $restauranteId,
            ];
        });
    }
}
