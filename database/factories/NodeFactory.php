<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Node>
 */
class NodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parceiroId' => User::factory()->create(['tipo' => 'parceiro'])->id,
            'tipo' => fake()->randomElement(['restaurante', 'centro_distribuicao', 'ponto_entrega']),
            'identificador' => fake()->bothify('NODE-####'),
            'capacidade' => fake()->randomFloat(2, 10, 100),
            'status' => fake()->randomElement(['ativo', 'inativo', 'manutencao']),
            'regiaoId' => Region::factory()->create()->id,
            'posicaoAtual' => [
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'endereco' => fake()->address()
            ]
        ];
    }
}
