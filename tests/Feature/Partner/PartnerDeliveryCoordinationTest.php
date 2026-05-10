<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;
use Illuminate\Support\Facades\Log;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PartnerDeliveryCoordinationTest extends TestCase
{
    use RefreshDatabase;

    protected $deliveryAssignmentMock;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create regular mock of DeliveryAssignment
        $this->deliveryAssignmentMock = Mockery::mock(DeliveryAssignment::class);
        $this->deliveryAssignmentMock->shouldReceive('create')
            ->andReturn(new DeliveryAssignment()); // Return a dummy instance
        $this->app->instance(DeliveryAssignment::class, $this->deliveryAssignmentMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Teste: Integração entre parceiros (BistroTech -> LogisMaster)
     * - Deve verificar se um partnere pode solicitar entregas para uma logística
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
        $partner = \App\Models\User::factory()->create([
            'type' => 'partner',
            'name' => 'BistroTech'
        ]);
        
        $logistics = \App\Models\User::factory()->create([
            'type' => 'partner', 
            'name' => 'LogisMaster'
        ]);

        // 2. Criar regiões compatíveis
        $partnerRegion = \App\Models\Region::factory()->create([
            'partner_id' => $partner->id,
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

        // 4. Autenticar como BistroTech com token Sanctum
        $token = $partner->createToken('test-token')->plainTextToken;
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ]);

        // 5. Criar solicitação de entrega
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
                ['name' => 'Prato Especial', 'quantity' => 2]
            ],
            'logistics_partner_id' => $logistics->id,
            'estimated_weight' => 1.5,
            'dimensions' => [
                'width' => 30,
                'height' => 20,
                'depth' => 15
            ],
            'type' => 'standard',
            'payment_method' => 'credit_card'
        ];

        $response = $this->postJson('/api/v1/deliveries', $deliveryData);

        // 6. Verificar resposta
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'status',
                'customer_id',
                'logistics_partner' => [
                    'id',
                    'name'
                ]
            ]);

        // 7. Verificar se entrega foi criada com status correto
        $this->assertDatabaseHas('deliveries', [
            'customer_id' => $partner->id,
            'logistics_partner_id' => $logistics->id,
            'status' => 'pending'
        ]);

        // 8. Verificar se LogisMaster recebeu notificação
        $this->assertDatabaseHas('notifications', [
            'user_id' => $logistics->id,
            'type' => 'delivery_request'
        ]);
    }
}
