<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryStatusUpdatesTest extends DeliveryBaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createDelivery();
    }

    public function testDeliveryStatusUpdates()
    {
        // Assign courier to delivery first
        $this->delivery->update(['courier_id' => $this->courier->id]);
        
        $statuses = ['in_transit', 'delivered'];
        foreach ($statuses as $status) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->courierToken
            ])->patchJson("/api/v1/deliveries/{$this->delivery->id}/status", [
                'status' => $status,
                'current_position' => [
                    'lat' => -23.5500 + (rand(0, 100)/1000),
                    'lng' => -46.6300 + (rand(0, 100)/1000)
                ]
            ]);
            
            $response->assertStatus(200)
                ->assertJsonPath('status', $status);
                
            $this->delivery->refresh();
            $this->assertEquals($status, $this->delivery->status);
            $this->assertEquals((string)$this->courier->id, $this->delivery->courier_id);
        }
    }
}
