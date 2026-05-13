<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryOriginTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $partner;
    private string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'type' => 'customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('Password123'),
        ]);

        $this->partner = User::factory()->create([
            'type' => 'partner',
            'email' => 'partner@example.com',
            'address' => 'Rua do Restaurante, 100',
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ]);

        $this->customerToken = $this->customer->createToken('customer-token')->plainTextToken;

        Product::factory()->create([
            'partner_id' => $this->partner->id,
            'price' => 10.00,
            'status' => 'available',
        ]);
    }

    public function test_order_origin_includes_partner_lat_lng()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->postJson('/api/v1/orders', [
            'partner_id' => $this->partner->id,
            'items' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                    'addons' => [],
                ],
            ],
            'payment' => [
                'method' => 'credit_card',
            ],
            'delivery' => [
                'destination' => [
                    'lat' => -23.5614,
                    'lng' => -46.6559,
                    'address' => 'Rua Augusta, 500, São Paulo, SP',
                ],
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('deliveries', [
            'customer_id' => $this->customer->id,
        ]);

        $delivery = \App\Models\Delivery::where('customer_id', $this->customer->id)->first();
        $origin = $delivery->origin;

        $this->assertEquals(-23.5505, $origin['lat']);
        $this->assertEquals(-46.6333, $origin['lng']);
        $this->assertEquals('Rua do Restaurante, 100', $origin['address']);
    }

    public function test_order_origin_handles_null_partner_coordinates()
    {
        $partnerNoCoords = User::factory()->create([
            'type' => 'partner',
            'email' => 'partner2@example.com',
            'address' => 'Rua Sem Coordenadas',
            'latitude' => null,
            'longitude' => null,
        ]);

        $product = Product::factory()->create([
            'partner_id' => $partnerNoCoords->id,
            'price' => 10.00,
            'status' => 'available',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken,
        ])->postJson('/api/v1/orders', [
            'partner_id' => $partnerNoCoords->id,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'addons' => [],
                ],
            ],
            'payment' => [
                'method' => 'pix',
            ],
            'delivery' => [
                'destination' => [
                    'lat' => -23.5614,
                    'lng' => -46.6559,
                    'address' => 'Rua Augusta, 500, São Paulo, SP',
                ],
            ],
        ]);

        $response->assertStatus(201);

        $delivery = \App\Models\Delivery::where('customer_id', $this->customer->id)
            ->latest()
            ->first();

        $origin = $delivery->origin;
        $this->assertNull($origin['lat']);
        $this->assertNull($origin['lng']);
        $this->assertEquals('Rua Sem Coordenadas', $origin['address']);
    }
}
