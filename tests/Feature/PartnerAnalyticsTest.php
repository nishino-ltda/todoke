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
            'type' => 'partner',
            'name' => 'BistroTech'
        ]);

        $customer = \App\Models\User::factory()->create([
            'type' => 'customer'
        ]);

        // 2. Criar entregas históricas
        \App\Models\Delivery::factory()->create([
            'customer_id' => $customer->id,
            'logisticsPartnerId' => $partner->id,
            'status' => 'completed',
            'created_at' => now()->subDays(3),
            'deliveryTime' => 30,
            'rating' => 5
        ]);

        \App\Models\Delivery::factory()->create([
            'customer_id' => $customer->id,
            'logisticsPartnerId' => $partner->id,
            'status' => 'completed',
            'created_at' => now()->subDays(1),
            'deliveryTime' => 45,
            'rating' => 4
        ]);

        // 3. Autenticar como parceiro
        $this->actingAs($partner);

        // 4. Acessar endpoint de métricas
        $response = $this->getJson('/api/partner/metrics');

        // 5. Verificar resposta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'totalDeliveries',
                    'averageDeliveryTime',
                    'averageRating',
                    'deliveriesPerDay'
                ]
            ]);

        // 6. Verificar valores calculados
        $response->assertJson([
            'data' => [
                'totalDeliveries' => 2,
                'averageDeliveryTime' => 37.5,
                'averageRating' => 4.5
            ]
        ]);
    }
}
