<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Delivery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'clienteId' => User::factory()->create(['tipo' => 'cliente'])->id,
            'restauranteId' => User::factory()->create(['tipo' => 'parceiro'])->id,
            'status' => fake()->randomElement(['em_analise', 'aceito', 'em_preparo', 'aguardando_entregador', 'entregador_retirou', 'entregue', 'cancelado']),
            'valorTotal' => fake()->randomFloat(2, 10, 200),
            'entregaId' => Delivery::factory()->create()->id,
        ];
    }
}
