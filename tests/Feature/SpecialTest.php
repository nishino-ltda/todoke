<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpecialTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function delivery_with_max_values_is_accepted(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->postJson('/api/v1/deliveries', [
            'origin' => ['lat' => -90, 'lng' => -180, 'address' => 'Max values'],
            'destination' => ['lat' => 90, 'lng' => 180, 'address' => 'Max values'],
            'item_description' => str_repeat('a', 255),
            'estimated_weight' => 999.99,
            'dimensions' => [
                'width' => 999,
                'height' => 999,
                'depth' => 999
            ],
            'type' => 'express',
            'payment_method' => 'credit_card'
        ]);

        $response->assertStatus(201);
    }

    #[Test]
    public function cannot_create_delivery_with_invalid_status(): void
    {
        $user = User::factory()->create();
        $delivery = Delivery::factory()->create(['customer_id' => $user->id]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer " . $user->createToken('test')->plainTextToken
        ])->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(400);
    }

    #[Test] 
    public function order_with_zero_items_is_rejected(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create(['type' => 'partner']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer " . $user->createToken('test')->plainTextToken
        ])->postJson('/api/v1/orders', [
            'partnerId' => $partner->id,
            'itens' => []
        ]);

        $response->assertStatus(400);
    }

    #[Test]
    public function system_recovers_from_database_failure(): void
    {
        // Simular falha no banco de dados
        $mock = \Mockery::mock('Illuminate\Database\Connection');
        $mock->shouldReceive('beginTransaction')->andThrow(new \Exception('Database error'));
        
        DB::shouldReceive('connection')->andReturn($mock);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '11999999999',
            'type' => 'customer',
            'senha' => 'password'
        ]);

        // Verifica apenas que o sistema retornou um erro 500
        // e que contém alguma mensagem de erro (sem especificar o texto exato)
        $response->assertStatus(500);
        $this->assertNotEmpty($response->json('message'));
    }

    #[Test]
    public function cannot_accept_already_accepted_delivery(): void
    {
        $user = User::factory()->create(['type' => 'courier']);
        $delivery = Delivery::factory()->create([
            'courier_id' => $user->id,
            'status' => 'in_transit'
        ]);

        $response = $this->withHeaders([
            'Authorization' => "Bearer " . $user->createToken('test')->plainTextToken
        ])->patchJson("/api/v1/deliveries/{$delivery->id}/accept");

        $response->assertStatus(409);
    }

    #[Test]
    public function order_with_invalid_product_is_rejected(): void
    {
        $user = User::factory()->create();
        $partner = User::factory()->create(['type' => 'partner']);

        $response = $this->withHeaders([
            'Authorization' => "Bearer " . $user->createToken('test')->plainTextToken
        ])->postJson('/api/v1/orders', [
            'partnerId' => $partner->id,
            'itens' => [
                ['produtoId' => 'invalid-uuid', 'quantidade' => 1]
            ]
        ]);

        $response->assertStatus(400);
    }
}
