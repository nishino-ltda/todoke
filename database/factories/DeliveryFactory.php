<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Node;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Delivery>
 */
class DeliveryFactory extends Factory
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
            'entregadorId' => User::factory()->create(['tipo' => 'entregador'])->id,
            'origem' => [
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'endereco' => fake()->address()
            ],
            'destino' => [
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'endereco' => fake()->address()
            ],
            'status' => fake()->randomElement(['pendente', 'aceito', 'em_transporte', 'entregue', 'cancelado']),
            'tipo' => fake()->randomElement(['normal', 'expressa', 'sustentavel']),
            'descricaoItem' => fake()->sentence(),
            'pesoEstimado' => fake()->randomFloat(2, 0.1, 10),
            'dimensoes' => [
                'altura' => fake()->randomFloat(2, 5, 50),
                'largura' => fake()->randomFloat(2, 5, 50),
                'profundidade' => fake()->randomFloat(2, 5, 50)
            ],
            'valor' => fake()->randomFloat(2, 5, 50),
            'tempoEstimado' => fake()->numberBetween(10, 120),
            'codigoConfirmacao' => fake()->bothify('????-####'),
            'nodeId' => Node::factory()->create()->id
        ];
    }
}
