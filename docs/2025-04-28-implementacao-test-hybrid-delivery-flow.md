# Implementação Detalhada do Teste de Fluxo de Entrega Híbrida

## Implementação do Teste

### 1. Modificação do Teste Existente

Abaixo está a implementação detalhada do teste `test_hybrid_delivery_flow` modificado, seguindo os princípios de TDD:

```php
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
        'logistics_partner_id' => $motoboy->id, // Especificar o parceiro inicial
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
                    'status',
                    'partner_id', // Verificar se o parceiro foi associado
                    'node_id'     // Verificar se o node foi associado
                ]
            ]
        ]);

    // 7. Verificar se a entrega foi dividida em duas etapas
    $delivery = \App\Models\Delivery::where('customer_id', $partner->id)->first();
    $this->assertNotNull($delivery->stages);
    $stages = is_array($delivery->stages) ? $delivery->stages : json_decode($delivery->stages, true);
    
    // Verificações específicas sobre as etapas
    $this->assertIsArray($stages);
    $this->assertCount(2, $stages);
    $this->assertEquals('delivery_point', $stages[0]['type']);
    $this->assertEquals('distribution_center', $stages[1]['type']);
    
    // Verificar se os parceiros foram associados corretamente
    $this->assertEquals($motoboy->id, $stages[0]['partner_id']);
    $this->assertEquals($drone->id, $stages[1]['partner_id']);
    
    // Verificar se os nodes foram associados corretamente
    $this->assertEquals($motoboyNode->id, $stages[0]['node_id']);
    $this->assertEquals($droneHub->id, $stages[1]['node_id']);

    // 8. Verificar se os assignments foram criados automaticamente
    $this->assertDatabaseHas('delivery_assignments', [
        'delivery_id' => $delivery->id,
        'partner_id' => $motoboy->id,
        'stage' => 1,
        'status' => 'pending'
    ]);

    $this->assertDatabaseHas('delivery_assignments', [
        'delivery_id' => $delivery->id,
        'partner_id' => $drone->id,
        'stage' => 2,
        'status' => 'pending'
    ]);
}
```

### 2. Novo Teste para Atualizações de Status

```php
public function test_hybrid_delivery_status_updates()
{
    // 1. Criar parceiros e entrega híbrida
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

    $customer = \App\Models\User::factory()->create([
        'type' => 'customer'
    ]);

    // 2. Criar nodes
    $motoboyNode = \App\Models\Node::factory()->create([
        'partner_id' => $motoboy->id,
        'type' => 'delivery_point',
        'status' => 'active'
    ]);

    $droneHub = \App\Models\Node::factory()->create([
        'partner_id' => $drone->id,
        'type' => 'distribution_center',
        'status' => 'active'
    ]);

    // 3. Criar entrega híbrida
    $delivery = \App\Models\Delivery::factory()->create([
        'customer_id' => $customer->id,
        'logistics_partner_id' => $motoboy->id,
        'status' => 'accepted',
        'stages' => [
            [
                'type' => 'delivery_point',
                'status' => 'pending',
                'partner_id' => $motoboy->id,
                'node_id' => $motoboyNode->id
            ],
            [
                'type' => 'distribution_center',
                'status' => 'pending',
                'partner_id' => $drone->id,
                'node_id' => $droneHub->id
            ]
        ]
    ]);

    // 4. Criar assignments
    \App\Models\DeliveryAssignment::create([
        'delivery_id' => $delivery->id,
        'partner_id' => $motoboy->id,
        'stage' => 1,
        'status' => 'pending'
    ]);

    \App\Models\DeliveryAssignment::create([
        'delivery_id' => $delivery->id,
        'partner_id' => $drone->id,
        'stage' => 2,
        'status' => 'pending'
    ]);

    // 5. Autenticar como motoboy
    $token = $motoboy->createToken('test-token')->plainTextToken;
    $this->withHeaders([
        'Authorization' => 'Bearer ' . $token
    ]);

    // 6. Atualizar status da primeira etapa (motoboy)
    $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
        'status' => 'in_transit',
        'current_position' => [-54.6200, -20.4550],
        'stage_type' => 'delivery_point'
    ]);
    
    $response->assertStatus(200);
    
    // Verificar se o status da etapa foi atualizado
    $updatedDelivery = \App\Models\Delivery::find($delivery->id);
    $stages = $updatedDelivery->stages;
    $this->assertEquals('in_transit', $stages[0]['status']);
    $this->assertEquals('pending', $stages[1]['status']);
    
    // Verificar se o assignment foi atualizado
    $this->assertDatabaseHas('delivery_assignments', [
        'delivery_id' => $delivery->id,
        'partner_id' => $motoboy->id,
        'stage' => 1,
        'status' => 'in_transit'
    ]);

    // 7. Marcar primeira etapa como concluída
    $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
        'status' => 'delivered',
        'current_position' => [-54.6150, -20.4500],
        'stage_type' => 'delivery_point'
    ]);
    
    $response->assertStatus(200);
    
    // Verificar se o status da etapa foi atualizado
    $updatedDelivery = \App\Models\Delivery::find($delivery->id);
    $stages = $updatedDelivery->stages;
    $this->assertEquals('delivered', $stages[0]['status']);
    $this->assertEquals('pending', $stages[1]['status']);
    
    // Verificar se o assignment foi atualizado
    $this->assertDatabaseHas('delivery_assignments', [
        'delivery_id' => $delivery->id,
        'partner_id' => $motoboy->id,
        'stage' => 1,
        'status' => 'delivered'
    ]);

    // 8. Autenticar como drone
    $token = $drone->createToken('test-token')->plainTextToken;
    $this->withHeaders([
        'Authorization' => 'Bearer ' . $token
    ]);

    // 9. Atualizar status da segunda etapa (drone) - coletado
    $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
        'status' => 'collected',
        'current_position' => [-54.6150, -20.4500],
        'stage_type' => 'distribution_center'
    ]);
    
    $response->assertStatus(200);
    
    // 10. Atualizar status da segunda etapa (drone) - lançado
    $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
        'status' => 'drone_launched',
        'current_position' => [-54.6150, -20.4500],
        'stage_type' => 'distribution_center'
    ]);
    
    $response->assertStatus(200);
    
    // Verificar se o status da etapa foi atualizado
    $updatedDelivery = \App\Models\Delivery::find($delivery->id);
    $stages = $updatedDelivery->stages;
    $this->assertEquals('drone_launched', $stages[1]['status']);
    
    // 11. Atualizar status da segunda etapa (drone) - em rota
    $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
        'status' => 'drone_in_route',
        'current_position' => [-54.6140, -20.4490],
        'stage_type' => 'distribution_center'
    ]);
    
    $response->assertStatus(200);
    
    // 12. Atualizar status da segunda etapa (drone) - chegou
    $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
        'status' => 'drone_arrived',
        'current_position' => [-54.6135, -20.4485],
        'stage_type' => 'distribution_center'
    ]);
    
    $response->assertStatus(200);
    
    // 13. Atualizar status da segunda etapa (drone) - entregue
    $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
        'status' => 'delivered',
        'current_position' => [-54.6135, -20.4485],
        'stage_type' => 'distribution_center'
    ]);
    
    $response->assertStatus(200);
    
    // Verificar se o status da entrega foi atualizado para entregue
    $updatedDelivery = \App\Models\Delivery::find($delivery->id);
    $this->assertEquals('delivered', $updatedDelivery->status);
    
    // Verificar se ambos os assignments foram atualizados
    $this->assertDatabaseHas('delivery_assignments', [
        'delivery_id' => $delivery->id,
        'partner_id' => $motoboy->id,
        'stage' => 1,
        'status' => 'delivered'
    ]);
    
    $this->assertDatabaseHas('delivery_assignments', [
        'delivery_id' => $delivery->id,
        'partner_id' => $drone->id,
        'stage' => 2,
        'status' => 'delivered'
    ]);
    
    // Verificar se o cliente recebeu notificação
    $this->assertDatabaseHas('notifications', [
        'user_id' => $customer->id,
        'type' => 'delivery_updated'
    ]);
}
```

## Implementação do Código

### 1. Modificação do DeliveryStatusService

```php
class DeliveryStatusService
{
    private const VALID_STATUSES = [
        'pending',
        'accepted',
        'collected',
        'in_transit',
        'delivered',
        'canceled',
        // Novos status específicos para drones
        'drone_launched',
        'drone_in_route',
        'drone_arrived',
        'drone_returned'
    ];

    private const VALID_STAGE_TYPES = [
        'delivery_point',
        'distribution_center'
    ];

    // Resto do código existente...

    /**
     * Handle status updates for hybrid deliveries with stages
     */
    private function updateStageStatus(Delivery $delivery, string $status, string $stageType, ?array $position): Delivery
    {
        $stages = $delivery->stages;
        $allStagesComplete = true;
        $stageIndex = null;

        // Update the specific stage status
        foreach ($stages as $index => &$stage) {
            if ($stage['type'] === $stageType) {
                $stage['status'] = $status;
                $stageIndex = $index;
            }

            if (!in_array($stage['status'], ['completed', 'delivered'])) {
                $allStagesComplete = false;
            }
        }

        // Force delivered status if all stages are complete
        if ($allStagesComplete) {
            $status = 'delivered';
        }

        $updateData = [
            'stages' => $stages,
            'current_position' => $position
        ];

        // Update overall status based on stage status
        if ($allStagesComplete) {
            $updateData['status'] = 'delivered';
        } else {
            // Se for um status de drone, mantenha o status geral como in_transit
            if (in_array($status, ['drone_launched', 'drone_in_route', 'drone_arrived', 'drone_returned'])) {
                $updateData['status'] = 'in_transit';
            } else {
                $updateData['status'] = $status;
            }
        }

        $delivery->update($updateData);

        // Update the corresponding delivery assignment
        $stageNumber = $stageIndex + 1;
        
        // Map stage status to assignment status
        $assignmentStatus = match($status) {
            'collected' => 'in_transit',
            'in_transit' => 'in_transit',
            'delivered' => 'delivered',
            'drone_launched' => 'in_transit',
            'drone_in_route' => 'in_transit',
            'drone_arrived' => 'in_transit',
            'drone_returned' => 'delivered',
            default => $status
        };

        // Always update assignment status based on both stage status and delivery status
        $finalStatus = ($status === 'delivered' || $updateData['status'] === 'delivered') 
            ? 'delivered' 
            : $assignmentStatus;

        // Update the assignment
        DeliveryAssignment::where('delivery_id', $delivery->id)
            ->where('stage', $stageNumber)
            ->update(['status' => $finalStatus]);

        // Notificar o cliente
        $this->createNotification(
            $delivery->customer_id,
            'delivery_updated',
            [
                'delivery_id' => $delivery->id,
                'status' => $status,
                'message' => "Stage $stageType updated to: $status"
            ]
        );

        // Se a etapa atual foi concluída, notificar o próximo parceiro
        if ($status === 'delivered' && isset($stages[$stageIndex + 1])) {
            $nextStage = $stages[$stageIndex + 1];
            if (isset($nextStage['partner_id'])) {
                $this->createNotification(
                    $nextStage['partner_id'],
                    'stage_ready',
                    [
                        'delivery_id' => $delivery->id,
                        'stage' => $stageIndex + 2,
                        'message' => "Your stage is ready to start"
                    ]
                );
            }
        }

        return $delivery;
    }
}
```

### 2. Modificação do DeliveryController

```php
class DeliveryController extends Controller
{
    // Resto do código existente...

    private function prepareDeliveryData(Request $request, $user, float $value, int $estimatedTime): array
    {
        $deliveryData = [
            // Código existente...
        ];

        if ($request->isHybrid) {
            // Buscar nodes apropriados para cada etapa
            $motoboyNode = null;
            $droneNode = null;
            
            if (isset($request->logistics_partner_id)) {
                $motoboyNode = \App\Models\Node::where('partner_id', $request->logistics_partner_id)
                    ->where('type', 'delivery_point')
                    ->where('status', 'active')
                    ->first();
            }
            
            if (!$motoboyNode) {
                // Buscar qualquer node de entrega ativo
                $motoboyNode = \App\Models\Node::where('type', 'delivery_point')
                    ->where('status', 'active')
                    ->first();
            }
            
            // Buscar hub de drone mais próximo do destino
            $droneNode = \App\Models\Node::where('type', 'distribution_center')
                ->where('status', 'active')
                ->first();
            
            if ($motoboyNode && $droneNode) {
                $deliveryData['stages'] = [
                    [
                        'type' => 'delivery_point',
                        'status' => 'pending',
                        'partner_id' => $motoboyNode->partner_id,
                        'node_id' => $motoboyNode->id
                    ],
                    [
                        'type' => 'distribution_center',
                        'status' => 'pending',
                        'partner_id' => $droneNode->partner_id,
                        'node_id' => $droneNode->id
                    ]
                ];
                
                // Definir o parceiro logístico inicial
                $deliveryData['logistics_partner_id'] = $motoboyNode->partner_id;
            } else {
                // Fallback para estrutura básica se não encontrar nodes
                $deliveryData['stages'] = [
                    ['type' => 'delivery_point', 'status' => 'pending'],
                    ['type' => 'distribution_center', 'status' => 'pending']
                ];
            }
            
            Log::info('Setting hybrid delivery stages', ['stages' => $deliveryData['stages']]);
        } else {
            $deliveryData['stages'] = null;
        }

        return $deliveryData;
    }

    // Resto do código existente...
}
```

### 3. Modificação do Método acceptDelivery no DeliveryStatusService

```php
/**
 * Accept a delivery by a courier
 */
public function acceptDelivery(Delivery $delivery, string $courierId): Delivery
{
    if ($delivery->status !== 'pending') {
        throw new \InvalidArgumentException('Delivery has already been accepted');
    }

    $delivery->update([
        'courier_id' => $courierId,
        'status' => 'accepted'
    ]);

    // Create assignments for hybrid deliveries
    if ($delivery->stages && count($delivery->stages) > 0) {
        $this->createAssignments($delivery);
    }

    $this->createNotification(
        $delivery->customer_id,
        'delivery_updated',
        [
            'delivery_id' => $delivery->id,
            'status' => 'accepted',
            'message' => 'Your delivery has been accepted by a courier'
        ]
    );

    return $delivery;
}

/**
 * Create assignments for hybrid delivery stages
 */
private function createAssignments(Delivery $delivery): void
{
    foreach ($delivery->stages as $index => $stage) {
        $partnerId = $stage['partner_id'] ?? $delivery->logistics_partner_id;
        
        if (!$partnerId) {
            continue;
        }
        
        DeliveryAssignment::updateOrCreate([
            'delivery_id' => $delivery->id,
            'stage' => $index + 1
        ], [
            'partner_id' => $partnerId,
            'status' => 'pending'
        ]);
    }
}
```

## Validações Adicionais

### 1. Validação de Permissão para Atualizar Etapa

```php
// No DeliveryController, método updateStatus
$isAssignedCourier = (string)$delivery->courier_id === (string)$user->id;
$isLogisticsPartner = (string)$delivery->logistics_partner_id === (string)$user->id;
$isStagePartner = false;

// Verificar se o usuário é o parceiro responsável pela etapa específica
if ($request->has('stage_type') && $delivery->stages) {
    foreach ($delivery->stages as $stage) {
        if ($stage['type'] === $request->stage_type && 
            isset($stage['partner_id']) && 
            (string)$stage['partner_id'] === (string)$user->id) {
            $isStagePartner = true;
            break;
        }
    }
}

if (!$isAssignedCourier && !$isLogisticsPartner && !$isStagePartner) {
    return response()->json(['message' => 'Only the assigned courier, logistics partner, or stage partner can update the status'], 403);
}
```

### 2. Validação de Ordem de Etapas

```php
// No DeliveryStatusService, método updateStageStatus
// Verificar se a etapa anterior foi concluída
if ($stageIndex > 0 && !in_array($stages[$stageIndex - 1]['status'], ['delivered', 'completed'])) {
    throw new \InvalidArgumentException('Previous stage must be completed first');
}
```

## Testes de Borda

### 1. Teste para Cancelamento de Etapa

```php
public function test_hybrid_delivery_stage_cancellation()
{
    // Implementação similar ao test_hybrid_delivery_status_updates
    // Mas testando o cancelamento de uma etapa e verificando o comportamento
}
```

### 2. Teste para Falha de Drone

```php
public function test_hybrid_delivery_drone_failure()
{
    // Implementação similar ao test_hybrid_delivery_status_updates
    // Mas testando o que acontece quando um drone não pode completar a entrega
}
```

## Conclusão

Esta implementação detalhada fornece um guia completo para a implementação do teste de fluxo de entrega híbrida seguindo os princípios de TDD. As modificações propostas garantirão que o sistema suporte adequadamente o caso de uso 1.9 (Entrega Híbrida: Motoqueiro + Drone Hub) e melhorarão a qualidade do código e a cobertura de testes.
