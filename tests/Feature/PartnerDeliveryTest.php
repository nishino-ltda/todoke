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
            'tipo' => 'parceiro',
            'name' => 'BistroTech'
        ]);
        
        $logistics = \App\Models\User::factory()->create([
            'tipo' => 'parceiro', 
            'name' => 'LogisMaster'
        ]);

        // 2. Criar regiões compatíveis
        $restaurantRegion = \App\Models\Region::factory()->create([
            'parceiroId' => $restaurant->id,
            'poligono' => [
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
            'parceiroId' => $logistics->id,
            'poligono' => [
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
            'parceiroId' => $logistics->id,
            'regiaoId' => $logisticsRegion->id,
            'tipo' => 'entregador',
            'status' => 'ativo'
        ]);

        // 4. Autenticar como BistroTech
        $this->actingAs($restaurant);

        // 5. Criar solicitação de entrega
        $deliveryData = [
            'origem' => [-54.6254, -20.4567],
            'destino' => [-54.6150, -20.4500],
            'produtos' => [
                ['name' => 'Prato Especial', 'quantidade' => 2]
            ],
            'parceiroLogisticaId' => $logistics->id
        ];

        $response = $this->postJson('/api/deliveries', $deliveryData);

        // 6. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'clienteId',
                    'parceiroLogisticaId'
                ]
            ]);

        // 7. Verificar se entrega foi criada com status correto
        $this->assertDatabaseHas('deliveries', [
            'clienteId' => $restaurant->id,
            'parceiroLogisticaId' => $logistics->id,
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
            'tipo' => 'parceiro',
            'name' => 'BistroTech'
        ]);
        
        $motoboy = \App\Models\User::factory()->create([
            'tipo' => 'parceiro',
            'name' => 'MotoExpress'
        ]);

        $drone = \App\Models\User::factory()->create([
            'tipo' => 'parceiro',
            'name' => 'DroneTech'
        ]);

        // 2. Criar regiões
        $restaurantRegion = \App\Models\Region::factory()->create([
            'parceiroId' => $restaurant->id
        ]);

        $motoboyRegion = \App\Models\Region::factory()->create([
            'parceiroId' => $motoboy->id
        ]);

        $droneRegion = \App\Models\Region::factory()->create([
            'parceiroId' => $drone->id
        ]);

        // 3. Criar nodes (motoboy e hub de drone)
        $motoboyNode = \App\Models\Node::factory()->create([
            'parceiroId' => $motoboy->id,
            'regiaoId' => $motoboyRegion->id,
            'tipo' => 'entregador',
            'status' => 'ativo'
        ]);

        $droneHub = \App\Models\Node::factory()->create([
            'parceiroId' => $drone->id,
            'regiaoId' => $droneRegion->id,
            'tipo' => 'hub_drone',
            'status' => 'ativo'
        ]);

        // 4. Autenticar como restaurante
        $this->actingAs($restaurant);

        // 5. Criar entrega com destino em área de drone
        $deliveryData = [
            'origem' => [-54.6254, -20.4567],  // Coordenadas do restaurante
            'destino' => [-54.6150, -20.4500], // Coordenadas em área de drone
            'produtos' => [
                ['name' => 'Prato Especial', 'quantidade' => 1]
            ],
            'ehHibrida' => true
        ];

        $response = $this->postJson('/api/deliveries', $deliveryData);

        // 6. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'etapas'
                ]
            ]);

        // 7. Verificar se a entrega foi dividida em duas etapas
        $delivery = \App\Models\Delivery::first();
        $this->assertCount(2, $delivery->etapas);
        $this->assertEquals('motoboy', $delivery->etapas[0]['tipo']);
        $this->assertEquals('drone', $delivery->etapas[1]['tipo']);

        // 8. Verificar se os parceiros receberam suas partes
        $this->assertDatabaseHas('delivery_assignments', [
            'deliveryId' => $delivery->id,
            'parceiroId' => $motoboy->id,
            'etapa' => 1
        ]);

        $this->assertDatabaseHas('delivery_assignments', [
            'deliveryId' => $delivery->id,
            'parceiroId' => $drone->id,
            'etapa' => 2
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
            'tipo' => 'parceiro',
            'name' => 'BistroTech'
        ]);
        
        $drone = \App\Models\User::factory()->create([
            'tipo' => 'parceiro',
            'name' => 'DroneExpress'
        ]);

        $customer = \App\Models\User::factory()->create([
            'tipo' => 'cliente'
        ]);

        // 2. Criar entrega híbrida na etapa do drone
        $delivery = \App\Models\Delivery::factory()->create([
            'clienteId' => $customer->id,
            'parceiroLogisticaId' => $drone->id,
            'status' => 'in_transit',
            'etapas' => [
                ['tipo' => 'motoboy', 'status' => 'completed'],
                ['tipo' => 'drone', 'status' => 'pending']
            ]
        ]);

        // 3. Criar assignment para o drone
        \App\Models\DeliveryAssignment::create([
            'deliveryId' => $delivery->id,
            'parceiroId' => $drone->id,
            'etapa' => 2,
            'status' => 'pending'
        ]);

        // 4. Autenticar como DroneExpress
        $this->actingAs($drone);

        // 5. Simular coleta no hub
        $response = $this->putJson("/api/deliveries/{$delivery->id}/status", [
            'etapa' => 2,
            'status' => 'collected',
            'posicaoAtual' => [-54.6150, -20.4500]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'status' => 'in_transit'
        ]);
        $this->assertDatabaseHas('delivery_assignments', [
            'deliveryId' => $delivery->id,
            'status' => 'collected'
        ]);

        // 6. Simular drone em voo
        $response = $this->putJson("/api/deliveries/{$delivery->id}/status", [
            'etapa' => 2,
            'status' => 'in_flight',
            'posicaoAtual' => [-54.6140, -20.4490]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('delivery_assignments', [
            'deliveryId' => $delivery->id,
            'status' => 'in_flight'
        ]);

        // 7. Simular entrega concluída
        $response = $this->putJson("/api/deliveries/{$delivery->id}/status", [
            'etapa' => 2,
            'status' => 'delivered',
            'posicaoAtual' => [-54.6135, -20.4485]
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'status' => 'completed'
        ]);
        $this->assertDatabaseHas('delivery_assignments', [
            'deliveryId' => $delivery->id,
            'status' => 'delivered'
        ]);

        // 8. Verificar notificação para cliente
        $this->assertDatabaseHas('notifications', [
            'userId' => $customer->id,
            'type' => 'delivery_completed'
        ]);
    }
}
