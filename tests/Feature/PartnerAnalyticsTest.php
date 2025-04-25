<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;

class PartnerAnalyticsTest extends TestCase
{
    /**
     * Teste: Visualização de métricas pelo parceiro BistroTech
     * - Deve verificar se um parceiro restaurante pode acessar suas métricas
     * - Pré-condições: 
     *   - Parceiro com entregas históricas
     * - Ações:
     *   1. Acessar endpoint de métricas
     * - Resultados esperados:
     *   - Dados de entregas por período
     *   - Tempo médio de entrega
     *   - Avaliação média
     */
    public function test_partner_can_view_metrics()
    {
        // 1. Criar parceiro e cliente
        $partner = \App\Models\User::factory()->create([
            'tipo' => 'parceiro',
            'name' => 'BistroTech'
        ]);

        $customer = \App\Models\User::factory()->create([
            'tipo' => 'cliente'
        ]);

        // 2. Criar entregas históricas
        \App\Models\Delivery::factory()->create([
            'clienteId' => $customer->id,
            'parceiroLogisticaId' => $partner->id,
            'status' => 'completed',
            'created_at' => now()->subDays(3),
            'tempoEntrega' => 30,
            'avaliacao' => 5
        ]);

        \App\Models\Delivery::factory()->create([
            'clienteId' => $customer->id,
            'parceiroLogisticaId' => $partner->id,
            'status' => 'completed',
            'created_at' => now()->subDays(1),
            'tempoEntrega' => 45,
            'avaliacao' => 4
        ]);

        // 3. Autenticar como parceiro
        $this->actingAs($partner);

        // 4. Acessar endpoint de métricas
        $response = $this->getJson('/api/partner/metrics');

        // 5. Verificar resposta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'totalEntregas',
                    'tempoMedioEntrega',
                    'avaliacaoMedia',
                    'entregasPorDia'
                ]
            ]);

        // 6. Verificar valores calculados
        $response->assertJson([
            'data' => [
                'totalEntregas' => 2,
                'tempoMedioEntrega' => 37.5,
                'avaliacaoMedia' => 4.5
            ]
        ]);
    }
}
