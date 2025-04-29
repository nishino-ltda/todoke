<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class DeliveryBaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected string $customerToken;
    protected User $courier;
    protected string $courierToken;
    protected ?Delivery $delivery = null;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();

        // Mock Notification model
        $this->mockNotificationModel();

        $this->customer = User::factory()->create([
            'id' => 1,
            'type' => 'customer',
            'email' => 'customere@example.com',
            'password' => Hash::make('Password123')
        ]);

        $this->courier = User::factory()->courier()->create([
            'id' => 2,
            'name' => 'Courier Test'
        ]);

        // Generate tokens directly
        $this->customerToken = $this->customer->createToken('customer-token')->plainTextToken;
        $this->courierToken = $this->courier->createToken('courier-token')->plainTextToken;
    }

    protected function mockNotificationModel(): void
    {
        $this->instance(
            \App\Models\Notification::class,
            \Mockery::mock('overload:' . \App\Models\Notification::class, function ($mock) {
                $mock->shouldReceive('create')->andReturn(true);
            })
        );
    }

    protected function createDelivery(): Delivery
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', [
            'origin' => ['lat' => -23.5505, 'lng' => -46.6333, 'address' => 'Av. Paulista, 1000'],
            'destination' => ['lat' => -23.5614, 'lng' => -46.6559, 'address' => 'Rua Augusta, 500'],
            'item_description' => 'Documentos importantes',
            'estimated_weight' => 0.5,
            'dimensions' => ['width' => 25, 'height' => 5, 'depth' => 15],
            'type' => 'standard',
            'payment_method' => 'credit_card'
        ]);

        $this->delivery = Delivery::find($response->json('id'));
        return $this->delivery;
    }
}
