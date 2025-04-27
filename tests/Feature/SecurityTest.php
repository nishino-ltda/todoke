<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;


class SecurityTest extends TestCase
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

        // Create two customer users with unique emails
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

        // Authenticate both customers using their actual emails
        $this->customer1Token = $this->postJson('/api/v1/auth/login', [
            'email' => $this->customer1->email,
            'password' => 'Password123'
        ])->json('token');

        $this->customer2Token = $this->postJson('/api/v1/auth/login', [
            'email' => $this->customer2->email,
            'password' => 'Password123'
        ])->json('token');

        // Create a delivery for customer1
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
            'customer_id' => $this->customer2->id // Tentando mudar o dono da entrega
        ]);

        $response->assertStatus(405); // Método não permitido

        // Verificar se o customer_id não foi alterado
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

        $response->assertStatus(403); // Deve ser proibido
    }

    #[Test]
    public function user_cannot_forge_fields_in_payload()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customer1Token
        ])->patchJson("/api/v1/deliveries/{$this->delivery->id}/status", [
            'status' => 'in_transit',
            'current_position' => ['lat' => -23.5500, 'lng' => -46.6300],
            // Campos forjados:
            'customer_id' => $this->customer2->id,
            'value' => 0.01 // Tentando mudar o valor para quase nada
        ]);

        $response->assertStatus(403); // Proibido por conter campos não permitidos

        // Verificar se os campos não foram alterados
        $this->assertDatabaseHas('deliveries', [
            'id' => $this->delivery->id,
            'customer_id' => $this->customer1->id,
            'value' => $this->delivery->value
        ]);
    }

    #[Test]
    public function user_cannot_update_sensitive_fields_directly()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customer1Token
        ])->putJson("/api/v1/deliveries/{$this->delivery->id}", [
            'confirmation_code' => 'HACKED', // Tentando mudar código de confirmação
            'value' => 0.01 // Tentando mudar o valor
        ]);

        // O endpoint PUT não existe, então deve retornar 405 (Method Not Allowed)
        $response->assertStatus(405);

        // Verificar se os campos sensíveis não foram alterados
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
                'email' => $newEmail // Tentando mudar o email do cliente
            ]
        ]);

        $response->assertStatus(405); // Método não permitido

        // Verificar se o email não foi alterado
        $this->assertDatabaseHas('users', [
            'id' => $this->customer1->id,
            'email' => $this->customer1->email
        ]);
    }
}
