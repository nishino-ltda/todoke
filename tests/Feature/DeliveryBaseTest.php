<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use App\Services\NotificationServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class DeliveryBaseTest extends TestCase
{
    use RefreshDatabase {
        refreshDatabase as baseRefreshDatabase;
    }

    protected User $customer;
    protected string $customerToken;
    protected User $courier;
    protected string $courierToken;
    protected ?Delivery $delivery = null;
    protected $notificationServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock NotificationServiceInterface
        $this->notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $this->notificationServiceMock->shouldReceive('createDeliveryNotification')
            ->withAnyArgs()
            ->andReturn(true);
        
        $this->app->instance(NotificationServiceInterface::class, $this->notificationServiceMock);

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

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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
