<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'type' => 'customer',
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_can_list_addresses()
    {
        Address::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/customer/addresses');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_create_address()
    {
        $addressData = [
            'label' => 'Casa',
            'address' => 'Rua Augusta, 500',
            'complement' => 'Apto 42',
            'neighborhood' => 'Consolação',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01304-000',
            'lat' => -23.5505,
            'lng' => -46.6333,
            'is_default' => true,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/customer/addresses', $addressData);

        $response->assertStatus(201);
        $this->assertEquals('Casa', $response->json('data.label'));
        $this->assertEquals('Rua Augusta, 500', $response->json('data.address'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'label' => 'Casa',
            'is_default' => true,
        ]);
    }

    public function test_cannot_create_address_without_required_fields()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/customer/addresses', []);

        $response->assertStatus(422);
    }

    public function test_can_update_address()
    {
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
            'label' => 'Casa',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/v1/customer/addresses/{$address->id}", [
            'label' => 'Trabalho',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('Trabalho', $response->json('data.label'));
    }

    public function test_can_delete_address()
    {
        $address = Address::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/v1/customer/addresses/{$address->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_can_set_default_address()
    {
        $addr1 = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);

        $addr2 = Address::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => false,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->patchJson("/api/v1/customer/addresses/{$addr2->id}/default");

        $response->assertStatus(200);

        $this->assertDatabaseHas('addresses', [
            'id' => $addr2->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('addresses', [
            'id' => $addr1->id,
            'is_default' => false,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_addresses()
    {
        $response = $this->getJson('/api/v1/customer/addresses');
        $response->assertStatus(401);
    }
}
