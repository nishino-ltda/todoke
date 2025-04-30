<?php

namespace Tests\Feature\Delivery;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Delivery\DeliveryBaseTest;

class DeliveryAcceptanceTest extends DeliveryBaseTest
{
    public function testDeliveryAcceptance()
    {
        $delivery = $this->createDelivery();
        $deliveryId = $delivery->id;

        // Accept the delivery
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$deliveryId}/accept");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'status', 'logistics_partner'])
            ->assertJsonPath('logistics_partner.id', (string)$this->courier->id);
            
        $deliveryRecord = Delivery::find($deliveryId);
        $this->assertEquals('accepted', $deliveryRecord->status);
        $this->assertEquals((string)$this->courier->id, $deliveryRecord->courier_id);
    }
}
