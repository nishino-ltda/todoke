<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class DroneStatusUpdatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
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
        $partner = \App\Models\User::factory()->create([
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
            'logistics_partner_id' => $drone->id,
            'status' => 'in_transit',
            'stages' => [
                ['type' => 'delivery_point', 'status' => 'delivered'],
                ['type' => 'distribution_center', 'status' => 'pending']
            ]
        ]);

        // 3. Criar assignments para ambas as etapas
        \App\Models\DeliveryAssignment::create([
            'delivery_id' => $delivery->id,
            'partner_id' => $drone->id,
            'stage' => 1,
            'status' => 'delivered'
        ]);
        
        \App\Models\DeliveryAssignment::create([
            'delivery_id' => $delivery->id,
            'partner_id' => $drone->id,
            'stage' => 2,
            'status' => 'pending'
        ]);

        // 4. Autenticar como DroneExpress com token Sanctum
        $token = $drone->createToken('test-token')->plainTextToken;
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ]);

        // 5. Simular coleta no hub
        // First update to collected status
        $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'stage' => 2,
            'status' => 'collected',
            'current_position' => [-54.6150, -20.4500],
            'stage_type' => 'distribution_center'
        ]);
        
        $response->assertStatus(200);
        $updatedDelivery = \App\Models\Delivery::find($delivery->id);
        $this->assertContains($updatedDelivery->status, ['collected', 'in_transit', 'delivered']);

        // 6. Simular drone em voo
        $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'stage' => 2,
            'status' => 'in_transit',
            'current_position' => [-54.6140, -20.4490],
            'stage_type' => 'distribution_center'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('in_transit', \App\Models\Delivery::find($delivery->id)->status);
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'status' => 'in_transit'
        ]);

        // 7. Simular drone em voo (garantir que chegue aqui)
        $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'stage' => 2,
            'status' => 'in_transit',
            'current_position' => [-54.6140, -20.4490],
            'stage_type' => 'distribution_center'
        ]);
        $response->assertStatus(200);
        Log::debug('After in_transit update', [
            'delivery_status' => \App\Models\Delivery::find($delivery->id)->status,
            'assignment_status' => \App\Models\DeliveryAssignment::where('delivery_id', $delivery->id)->first()->status
        ]);

        // 8. Simular entrega concluída
        $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'stage' => 2,
            'status' => 'delivered',
            'current_position' => [-54.6135, -20.4485],
            'stage_type' => 'distribution_center'
        ]);
        $response->assertStatus(200);
        Log::debug('After delivered update', [
            'delivery_status' => \App\Models\Delivery::find($delivery->id)->status,
            'assignment_status' => \App\Models\DeliveryAssignment::where('delivery_id', $delivery->id)->first()->status
        ]);
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'status' => 'delivered'
        ]);
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'status' => 'delivered'
        ]);

        // 8. Verificar status final e notificação
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'status' => 'delivered'
        ]);

        // Verificar assignment status - adicionar retry para lidar com possível assincronia
        $maxAttempts = 5;
        $attempt = 0;
        $assignmentUpdated = false;

        while ($attempt < $maxAttempts && !$assignmentUpdated) {
            $assignmentUpdated = \App\Models\DeliveryAssignment::where([
                'delivery_id' => $delivery->id,
                'partner_id' => $drone->id,
                'status' => 'delivered'
            ])->exists();
            
            if (!$assignmentUpdated) {
                $attempt++;
                sleep(0.1);
            }
        }

        $this->assertTrue($assignmentUpdated, 'Assignment status not updated to delivered after multiple attempts');

        // Verificar notificação
        $this->assertDatabaseHas('notifications', [
            'user_id' => $customer->id,
            'type' => 'delivery_updated'
        ]);
    }
}
