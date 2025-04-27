<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;

class PartnerAnalyticsTest extends TestCase
{
    /**
     * Teste: Visualização de métricas pelo parceiro BistroTech
     * - Deve verificar se um parceiro partner pode acessar suas métricas
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
        $delivery1 = \App\Models\Delivery::factory()->create([
            'customer_id' => $customer->id,
            'logistics_partner_id' => $partner->id,
            'status' => 'delivered',
            'created_at' => now()->subDays(3),
            'estimated_time' => 30
        ]);

        \App\Models\Rating::factory()->create([
            'delivery_id' => $delivery1->id,
            'rater_id' => $customer->id,
            'rated_id' => $partner->id,
            'rating' => 5
        ]);

        $delivery2 = \App\Models\Delivery::factory()->create([
            'customer_id' => $customer->id,
            'logistics_partner_id' => $partner->id,
            'status' => 'delivered',
            'created_at' => now()->subDays(1),
            'estimated_time' => 45
        ]);

        \App\Models\Rating::factory()->create([
            'delivery_id' => $delivery2->id,
            'rater_id' => $customer->id,
            'rated_id' => $partner->id,
            'rating' => 4
        ]);

        // 3. Autenticar como parceiro
        $this->actingAs($partner);

        // 4. Acessar endpoint de métricas
        $response = $this->getJson('/api/v1/partner/metrics');

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
