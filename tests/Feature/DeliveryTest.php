<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private string $customerToken;
    private User $courier; 
    private string $courierToken;

    // Set up the test environment
    protected function setUp(): void
    {
        parent::setUp();

        // Create a customer user
        $this->customer = User::factory()->create([
            'id' => 1,
            'type' => 'customer',
            'email' => 'customere@example.com',
            'password' => Hash::make('Password123')
        ]);

        // Create a delivery person user
        $this->courier = User::factory()->courier()->create([
            'id' => 2,
            'name' => 'Courier Test'
        ]);

        // Authenticate the customer and retrieve the token
        $customerLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'customere@example.com',
            'password' => 'Password123'
        ]);
        $this->customerToken = $customerLogin->json('token');

        // Authenticate the delivery person and retrieve the token
        $courierLogin = $this->postJson('/api/v1/auth/login', [
            'email' => $this->courier->email,
            'password' => 'Password123'
        ]);
        $this->courierToken = $courierLogin->json('token');
    }

    // Test the creation of a delivery
    public function testDeliveryCreation()
    {
        $baseData = [
            'origin' => [
                'lat' => -23.5505,
                'lng' => -46.6333,
                'address' => 'Av. Paulista, 1000'
            ],
            'destination' => [
                'lat' => -23.5614,
                'lng' => -46.6559, 
                'address' => 'Rua Augusta, 500'
            ],
            'item_description' => 'Documentos importantes',
            'estimated_weight' => 0.5,
            'dimensions' => [
                'width' => 25,
                'height' => 5,
                'depth' => 15
            ]
        ];

        // Test standard delivery creation
        $standardData = array_merge($baseData, ['type' => 'standard']);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', $standardData);

        if ($response->status() === 400) {
            dump($response->json());
        }
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'value', 'estimated_time', 'confirmation_code', 'status'
            ])
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('customer_id', $this->customer->id)
            ->assertJsonPath('confirmation_code', function ($code) {
                return preg_match('/^[A-Z0-9]{6}$/', $code) === 1;
            });
            
        $this->assertDatabaseHas('deliveries', [
            'customer_id' => $this->customer->id,
            'status' => 'pending'
        ]);

        $standardValue = $response->json('value');
        $standardTime = $response->json('estimated_time');

        // Test express delivery creation
        $expressData = array_merge($baseData, ['type' => 'express']);
        $expressResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', $expressData);
        
        $expressResponse->assertStatus(201);
        $this->assertGreaterThan($standardValue, $expressResponse->json('value'));
        $this->assertLessThan($standardTime, $expressResponse->json('estimated_time'));

        // Test invalid delivery data
        $invalidData = $baseData;
        $invalidData['estimated_weight'] = -1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', $invalidData);
        
        $response->assertStatus(400)
            ->assertJson([
                'estimated_weight' => ['The estimated weight field must be at least 0.']
            ]);

        // Test out-of-coverage delivery
        $outOfCoverageData = $baseData;
        $outOfCoverageData['destination']['lat'] = -22.0000;
        $outOfCoverageData['destination']['lng'] = -43.0000;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', $outOfCoverageData);
        $response->assertStatus(400);
    }

    // Test acceptance and completion of a delivery
    public function testDeliveryAcceptanceAndCompletion()
    {
        // Create a delivery
        $delivery = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', [
            'origin' => ['lat' => -23.5505, 'lng' => -46.6333, 'address' => 'Av. Paulista, 1000'],
            'destination' => ['lat' => -23.5614, 'lng' => -46.6559, 'address' => 'Rua Augusta, 500'],
            'item_description' => 'Documentos importantes',
            'estimated_weight' => 0.5,
            'dimensions' => ['width' => 25, 'height' => 5, 'depth' => 15],
            'type' => 'standard'
        ])->json();

        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery['id'],
            'status' => 'pending',
            'customer_id' => $this->customer->id
        ]);

        // Accept the delivery
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$delivery['id']}/accept");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'status', 'logistics_partner'])
            ->assertJsonPath('logistics_partner.id', (string)$this->courier->id);
            
        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery['id'],
            'logistics_partner_id' => (string)$this->courier->id,
            'status' => 'accepted'
        ]);

        // Update delivery status to 'in_transit' and 'delivered'
        $statuses = ['in_transit', 'delivered'];
        foreach ($statuses as $status) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->courierToken
            ])->patchJson("/api/v1/deliveries/{$delivery['id']}/status", [
                'status' => $status,
                'current_position' => [
                    'lat' => -23.5500 + (rand(0, 100)/1000),
                    'lng' => -46.6300 + (rand(0, 100)/1000)
                ]
            ]);
            
            $response->assertStatus(200)
                ->assertJsonPath('status', $status);
                
            $this->assertDatabaseHas('deliveries', [
                'id' => $delivery['id'],
                'status' => $status,
                'logistics_partner_id' => (string)$this->courier->id
            ]);
        }

        // Complete the delivery with the correct confirmation code
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$delivery['id']}/status", [
            'status' => 'delivered',
            'confirmation_code' => $delivery['confirmation_code'],
            'current_position' => ['lat' => -23.5614, 'lng' => -46.6559]
        ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('status', 'delivered');

        // Attempt to complete the delivery with an incorrect confirmation code
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$delivery['id']}/status", [
            'status' => 'delivered',
            'confirmation_code' => 'CODIGO_INCORRETO'
        ]);
        
        $response->assertStatus(400);
    }

    // Test tracking of a delivery
    public function testDeliveryTracking()
    {
        // Create a delivery
        $delivery = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', [
            'origin' => ['lat' => -23.5505, 'lng' => -46.6333, 'address' => 'Av. Paulista, 1000'],
            'destination' => ['lat' => -23.5614, 'lng' => -46.6559, 'address' => 'Rua Augusta, 500'],
            'item_description' => 'Documentos importantes',
            'estimated_weight' => 0.5,
            'dimensions' => ['width' => 25, 'height' => 5, 'depth' => 15],
            'type' => 'standard'
        ])->json();

        // Accept the delivery
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$delivery['id']}/accept");

        // Check delivery details
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->getJson("/api/v1/deliveries/{$delivery['id']}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'status', 'logistics_partner', 'current_position', 'status_history'
            ])
            ->assertJsonPath('status', 'accepted')
            ->assertJsonPath('logistics_partner.id', (string)$this->courier->id)
            ->assertJsonPath('logistics_partner.name', $this->courier->name);

        // Update delivery position
        $newPosition = ['lat' => -23.5555, 'lng' => -46.6444];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$delivery['id']}/status", [
            'status' => 'in_transit',
            'current_position' => $newPosition
        ]);

        // Verify updated position
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->getJson("/api/v1/deliveries/{$delivery['id']}");
        
        $response->assertJsonPath('current_position', $newPosition)
            ->assertJsonPath('status', 'in_transit');

        // Complete the delivery
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->patchJson("/api/v1/deliveries/{$delivery['id']}/status", [
            'status' => 'delivered',
            'confirmation_code' => $delivery['confirmation_code']
        ]);

        // Check notifications for the customer
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->getJson("/api/v1/notifications");
        
        $response->assertStatus(200)
            ->assertJsonFragment([
                'type' => 'delivery_updated',
                'delivery_id' => $delivery['id'],
                'status' => 'delivered'
            ]);

        // Send a message from the customer
        $message = ['text' => 'Where are you?'];
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson("/api/v1/deliveries/{$delivery['id']}/messages", $message);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'text', 'author']);

        // Retrieve messages as the delivery person
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->courierToken
        ])->getJson("/api/v1/deliveries/{$delivery['id']}/messages");
        
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment($message);
    }
}
