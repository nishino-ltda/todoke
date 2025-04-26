<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;

class PartnerDeliveryTest extends TestCase
{
    /**
     * Teste: Integração entre parceiros (BistroTech -> LogisMaster)
     * - Deve verificar se um restaurante pode solicitar entregas para uma logística
     * - Pré-condições: 
     *   - Ambos parceiros cadastrados
     *   - Regiões compatíveis
     * - Ações:
     *   1. BistroTech cria solicitação de entrega
     *   2. Sistema aloca recurso da LogisMaster
     * - Resultados esperados:
     *   - Solicitação criada com status "awaiting_confirmation"
     *   - LogisMaster recebe notificação
     *   - Após confirmação, entrega é agendada
     */
    public function test_inter_partner_delivery_coordination()
    {
        // 1. Criar parceiros (BistroTech e LogisMaster)
        $restaurant = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'BistroTech'
        ]);
        
        $logistics = \App\Models\User::factory()->create([
            'type' => 'partner', 
            'name' => 'LogisMaster'
        ]);

        // 2. Criar regiões compatíveis
        $restaurantRegion = \App\Models\Region::factory()->create([
            'partner_id' => $restaurant->id,
            'polygon' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [-54.6468, -20.4697],
                    [-54.5954, -20.4697],
                    [-54.5954, -20.4412],
                    [-54.6468, -20.4412],
                    [-54.6468, -20.4697]
                ]]
            ]
        ]);

        $logisticsRegion = \App\Models\Region::factory()->create([
            'partner_id' => $logistics->id,
            'polygon' => [
                'type' => 'Polygon',
                'coordinates' => [[
                    [-54.6468, -20.4697],
                    [-54.5954, -20.4697],
                    [-54.5954, -20.4412],
                    [-54.6468, -20.4412],
                    [-54.6468, -20.4697]
                ]]
            ]
        ]);

        // 3. Criar node de entrega para LogisMaster
        $deliveryNode = \App\Models\Node::factory()->create([
            'partner_id' => $logistics->id,
            'region_id' => $logisticsRegion->id,
            'type' => 'delivery_point',
            'status' => 'active'
        ]);

        // 4. Autenticar como BistroTech
        $this->actingAs($restaurant);

        // 5. Criar solicitação de entrega
        $deliveryData = [
            'origin' => [-54.6254, -20.4567],
            'destination' => [-54.6150, -20.4500],
            'products' => [
                ['name' => 'Prato Especial', 'quantity' => 2]
            ],
            'logisticsPartnerId' => $logistics->id
        ];

        $response = $this->postJson('/api/deliveries', $deliveryData);

        // 6. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'customer_id',
                    'logisticsPartnerId'
                ]
            ]);

        // 7. Verificar se entrega foi criada com status correto
        $this->assertDatabaseHas('deliveries', [
            'customer_id' => $restaurant->id,
            'logisticsPartnerId' => $logistics->id,
            'status' => 'awaiting_confirmation'
        ]);

        // 8. Verificar se LogisMaster recebeu notificação
        $this->assertDatabaseHas('notifications', [
            'userId' => $logistics->id,
            'type' => 'delivery_request'
        ]);
    }

    /**
     * Teste: Entrega híbrida (Moto + Drone)
     * - Deve verificar o fluxo de entrega combinando parceiros diferentes
     * - Pré-condições:
     *   - Parceiro motoboy cadastrado
     *   - Parceiro drone cadastrado
     *   - Hub de drone configurado
     * - Ações:
     *   1. Criar entrega com destino em área de drone
     *   2. Verificar roteamento automático para hub
     *   3. Verificar transferência para drone
     * - Resultados esperados:
     *   - Sistema divide a entrega em duas etapas
     *   - Ambos parceiros recebem suas partes da rota
     *   - Cliente recebe atualizações de ambas etapas
     */
    public function test_hybrid_delivery_flow()
    {
        // 1. Criar parceiros (restaurante, motoboy e drone)
        $restaurant = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'BistroTech'
        ]);
        
        $motoboy = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'MotoExpress'
        ]);

        $drone = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'DroneTech'
        ]);

        // 2. Criar regiões
        $restaurantRegion = \App\Models\Region::factory()->create([
            'partner_id' => $restaurant->id
        ]);

        $motoboyRegion = \App\Models\Region::factory()->create([
            'partner_id' => $motoboy->id
        ]);

        $droneRegion = \App\Models\Region::factory()->create([
            'partner_id' => $drone->id
        ]);

        // 3. Criar nodes (motoboy e hub de drone)
        $motoboyNode = \App\Models\Node::factory()->create([
            'partner_id' => $motoboy->id,
            'region_id' => $motoboyRegion->id,
            'type' => 'delivery_point',
            'status' => 'active'
        ]);

        $droneHub = \App\Models\Node::factory()->create([
            'partner_id' => $drone->id,
            'region_id' => $droneRegion->id,
            'type' => 'distribution_center',
            'status' => 'active'
        ]);

        // 4. Autenticar como restaurante
        $this->actingAs($restaurant);

        // 5. Criar entrega com destino em área de drone
        $deliveryData = [
            'origin' => [-54.6254, -20.4567],  // Restaurant coordinates
            'destination' => [-54.6150, -20.4500], // Drone area coordinates
            'products' => [
                ['name' => 'Prato Especial', 'quantity' => 1]
            ],
            'isHybrid' => true
        ];

        $response = $this->postJson('/api/deliveries', $deliveryData);

        // 6. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'stages'
                ]
            ]);

        // 7. Verificar se a entrega foi dividida em duas etapas
        $delivery = \App\Models\Delivery::first();
        $this->assertCount(2, $delivery->stages);
            $this->assertEquals('delivery_point', $delivery->stages[0]['type']);
            $this->assertEquals('distribution_center', $delivery->stages[1]['type']);

        // 8. Verificar se os parceiros receberam suas partes
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'partner_id' => $motoboy->id,
            'stage' => 1
        ]);

        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'partner_id' => $drone->id,
            'stage' => 2
        ]);
    }

    /**
     * Teste: Atualização de status pelo parceiro DroneExpress
     * - Deve verificar se um parceiro drone pode atualizar status de entrega
     * - Pré-condições: 
     *   - Entrega híbrida em andamento
     *   - Pacote no hub do drone
     * - Ações:
     *   1. DroneExpress atualiza status (coletado, em voo, entregue)
     *   2. Sistema propaga atualização
     * - Resultados esperados:
     *   - Status atualizado no banco de dados
     *   - Cliente recebe notificação
     *   - Motoboy recebe confirmação se aplicável
     */
    public function test_drone_partner_can_update_status()
    {
        // 1. Criar parceiros e entrega híbrida
        $restaurant = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'BistroTech'
        ]);
        
        $drone = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'DroneExpress'
        ]);

        $customer = \App\Models\User::factory()->create([
            'type' => 'customer'
        ]);

        // 2. Criar entrega híbrida na etapa do drone
        $delivery = \App\Models\Delivery::factory()->create([
            'customer_id' => $customer->id,
            'logisticsPartnerId' => $drone->id,
            'status' => 'in_transit',
            'stages' => [
                ['type' => 'delivery_point', 'status' => 'completed'],
                ['type' => 'distribution_center', 'status' => 'pending']
            ]
        ]);

        // 3. Criar assignment para o drone
        \App\Models\DeliveryAssignment::create([
            'delivery_id' => $delivery->id,
            'partner_id' => $drone->id,
            'stage' => 2,
            'status' => 'pending'
        ]);

        // 4. Autenticar como DroneExpress
        $this->actingAs($drone);

        // 5. Simular coleta no hub
        $response = $this->putJson("/api/deliveries/{$delivery->id}/status", [
            'stage' => 2,
            'status' => 'collected',
            'current_position' => [-54.6150, -20.4500]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'status' => 'in_transit'
        ]);
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'status' => 'collected'
        ]);

        // 6. Simular drone em voo
        $response = $this->putJson("/api/deliveries/{$delivery->id}/status", [
            'stage' => 2,
            'status' => 'in_flight',
            'current_position' => [-54.6140, -20.4490]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'status' => 'in_flight'
        ]);

        // 7. Simular entrega concluída
        $response = $this->putJson("/api/deliveries/{$delivery->id}/status", [
            'stage' => 2,
            'status' => 'delivered',
            'current_position' => [-54.6135, -20.4485]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'status' => 'completed'
        ]);
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'status' => 'delivered'
        ]);

        // 8. Verificar notificação para cliente
        $this->assertDatabaseHas('notifications', [
            'userId' => $customer->id,
            'type' => 'delivery_completed'
        ]);
    }
}
