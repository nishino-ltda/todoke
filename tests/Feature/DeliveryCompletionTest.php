<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryCompletionTest extends DeliveryBaseTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createDelivery();
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}/accept");
        
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}/status", [
            'status' => 'delivered',
            'current_position' => ['lat' => -23.5614, 'lng' => -46.6559]
        ]);
    }

    public function testDeliveryCompletionWithCorrectCode()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}/status", [
            'status' => 'delivered',
            'confirmation_code' => $this->delivery->confirmation_code,
            'current_position' => ['lat' => -23.5614, 'lng' => -46.6559]
        ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('status', 'delivered');
    }

    public function testDeliveryCompletionWithIncorrectCode()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}/status", [
            'status' => 'delivered',
            'confirmation_code' => 'CODIGO_INCORRETO'
        ]);
        
        $response->assertStatus(400);
    }
}
