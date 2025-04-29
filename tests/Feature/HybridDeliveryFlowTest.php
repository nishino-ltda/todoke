<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;
use Illuminate\Support\Facades\Log;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HybridDeliveryFlowTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Mockery::close();
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
         // 1. Criar parceiros (partner, motoboy e drone)
        $partner = \App\Models\User::factory()->create([
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
        $partnerRegion = \App\Models\Region::factory()->create([
            'partner_id' => $partner->id
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

         // 4. Autenticar como partner com token Sanctum
        $token = $partner->createToken('test-token')->plainTextToken;
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ]);

        // 5. Criar entrega com destino em área de drone
        $deliveryData = [
            'origin' => [
                'lat' => -54.6254,
                'lng' => -20.4567,
                'address' => 'Rua Teste, 123'
            ],
            'destination' => [
                'lat' => -54.6150,
                'lng' => -20.4500,
                'address' => 'Av. Teste, 456'
            ],
            'products' => [
                ['name' => 'Prato Especial', 'quantity' => 1]
            ],
            'estimated_weight' => 1.5,
            'dimensions' => [
                'width' => 30,
                'height' => 20,
                'depth' => 15
            ],
            'type' => 'standard',
            'isHybrid' => true,
            'payment_method' => 'credit_card'
        ];

        $response = $this->postJson('/api/v1/deliveries', $deliveryData);

        // 6. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'status',
                'customer_id',
                'stages' => [
                    '*' => [
                        'type',
                        'status'
                    ]
                ]
            ]);

        // 7. Verificar resposta completa
        $delivery = \App\Models\Delivery::where('customer_id', $partner->id)->first();
        $this->assertNotNull($delivery->stages);
        $stages = is_array($delivery->stages) ? $delivery->stages : json_decode($delivery->stages, true);
        
        // Verificações específicas sobre as etapas
        $this->assertIsArray($stages);
        $this->assertCount(2, $stages);
        $this->assertEquals('delivery_point', $stages[0]['type']);
        $this->assertEquals('distribution_center', $stages[1]['type']);
        
        // Verificar se os parceiros foram associados corretamente (usando IDs dinâmicos)
        $this->assertArrayHasKey('partner_id', $stages[0]);
        $this->assertArrayHasKey('partner_id', $stages[1]);
        $this->assertNotNull($stages[0]['partner_id']);
        $this->assertNotNull($stages[1]['partner_id']);
        
        // Verificar se os nodes foram associados corretamente (usando IDs dinâmicos)
        $this->assertArrayHasKey('node_id', $stages[0]);
        $this->assertArrayHasKey('node_id', $stages[1]);
        $this->assertNotNull($stages[0]['node_id']);
        $this->assertNotNull($stages[1]['node_id']);

        // 8. Verificar se os assignments foram criados automaticamente
        $assignments = \App\Models\DeliveryAssignment::where('delivery_id', $delivery->id)->get();
        $this->assertCount(2, $assignments);
        
        // Verificar se há assignments para cada stage
        $stage1Assignment = $assignments->where('stage', 1)->first();
        $stage2Assignment = $assignments->where('stage', 2)->first();
        
        $this->assertNotNull($stage1Assignment);
        $this->assertNotNull($stage2Assignment);
        $this->assertEquals('pending', $stage1Assignment->status);
        $this->assertEquals('pending', $stage2Assignment->status);
    }
}
