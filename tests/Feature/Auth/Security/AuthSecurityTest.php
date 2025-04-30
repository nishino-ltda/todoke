<?php

namespace Tests\Feature\Auth\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $customer1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer1 = User::factory()->create([
            'type' => 'customer',
            'email' => 'customer1_' . uniqid() . '@example.com',
            'password' => Hash::make('Password123')
        ]);
    }

    #[Test]
    public function test_unauthorized_access_to_sensitive_endpoints(): void
    {
        $sensitiveEndpoint = '/api/v1/deliveries';
        $deliveryData = [
            'customer_id' => $this->customer1->id,
            'origin' => ['lat' => -23.5505, 'lng' => -46.6333, 'address' => 'Av. Paulista, 1000'],
            'destination' => ['lat' => -23.5675, 'lng' => -46.6558, 'address' => 'Av. Brigadeiro Faria Lima, 1000'],
            'items' => [
                ['product_id' => 1, 'quantity' => 1, 'price' => 10.00]
            ],
            'value' => 10.00,
            'type' => 'standard',
            'payment_method' => 'credit_card'
        ];

        $response = $this->postJson($sensitiveEndpoint, $deliveryData);

        $response->assertStatus(401);
    }
}
