<?php

namespace Tests\Feature\Delivery\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class DeliverySecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $customer1;
    private string $customer1Token;
    private User $customer2;
    private string $customer2Token;
    private Delivery $delivery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer1 = User::factory()->create([
            'type' => 'customer',
            'email' => 'customer1_' . uniqid() . '@example.com',
            'password' => Hash::make('Password123')
        ]);

        $this->customer2 = User::factory()->create([
            'type' => 'customer',
            'email' => 'customer2_' . uniqid() . '@example.com',
            'password' => Hash::make('Password123')
        ]);

        $this->customer1Token = $this->customer1->createToken('customer1-token')->plainTextToken;
        $this->customer2Token = $this->customer2->createToken('customer2-token')->plainTextToken;

        $this->delivery = Delivery::factory()->create([
            'customer_id' => $this->customer1->id,
            'status' => 'pending'
        ]);
    }

    #[Test]
    public function user_cannot_change_delivery_customer_id()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customer2Token
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}", [
            'customer_id' => $this->customer2->id
        ]);

        $response->assertStatus(405);
        $this->assertDatabaseHas('deliveries', [
            'id' => $this->delivery->id,
            'customer_id' => $this->customer1->id
        ]);
    }

    #[Test]
    public function user_cannot_access_other_users_deliveries()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customer2Token
        ])->getJson("/api/v1/deliveries/{$this->delivery->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function user_cannot_forge_fields_in_payload()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customer1Token
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}/status", [
            'status' => 'in_transit',
            'current_position' => ['lat' => -23.5500, 'lng' => -46.6300],
            'customer_id' => $this->customer2->id,
            'value' => 0.01,
            'type' => 'priority',
            'payment_method' => 'pix'
        ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('deliveries', [
            'id' => $this->delivery->id,
            'customer_id' => $this->customer1->id,
            'value' => $this->delivery->value,
            'type' => $this->delivery->type,
            'payment_method' => $this->delivery->payment_method
        ]);
    }

    #[Test]
    public function user_cannot_update_sensitive_fields_directly()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customer1Token
        ])->putJson("/api/v1/deliveries/{$this->delivery->id}", [
            'confirmation_code' => 'HACKED',
            'value' => 0.01
        ]);

        $response->assertStatus(405);

        $this->assertDatabaseHas('deliveries', [
            'id' => $this->delivery->id,
            'confirmation_code' => $this->delivery->confirmation_code,
            'value' => $this->delivery->value
        ]);
    }

    #[Test]
    public function user_cannot_update_email_through_delivery_api()
    {
        $newEmail = 'hacked@example.com';
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customer1Token
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}", [
            'customer' => [
                'email' => $newEmail
            ]
        ]);

        $response->assertStatus(405);

        $this->assertDatabaseHas('users', [
            'id' => $this->customer1->id,
            'email' => $this->customer1->email
        ]);
    }

    #[Test]
    public function test_user_cannot_access_other_users_data()
    {
        $deliveryForCustomer1 = Delivery::factory()->create(['customer_id' => $this->customer1->id]);
        $this->actingAs($this->customer2, 'sanctum');

        $response = $this->getJson("/api/v1/deliveries/{$deliveryForCustomer1->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function test_prevents_forging_fields_in_payload()
    {
        $delivery = Delivery::factory()->create(['customer_id' => $this->customer1->id, 'value' => 100]);
        $forgedPayload = [
            'status' => 'delivered',
            'customer_id' => $this->customer2->id,
            'value' => 0.01,
        ];
        $this->actingAs($this->customer1, 'sanctum');

        $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", $forgedPayload);

        $response->assertStatus(403);
        $delivery->refresh();
        $this->assertEquals($this->customer1->id, $delivery->customer_id);
        $this->assertEquals(100, $delivery->value);
    }
}
