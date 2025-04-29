<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'id' => 1,
            'type' => 'customer',
            'email' => 'customere@example.com',
            'password' => Hash::make('Password123')
        ]);

        $customerLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'customere@example.com',
            'password' => 'Password123'
        ]);
        $this->customerToken = $customerLogin->json('token');
    }

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
            ],
            'special_instructions' => 'Entregar apenas para o responsável',
            'payment_method' => 'pix'
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
                'id', 'value', 'estimated_time', 'confirmation_code', 'status',
                'special_instructions', 'payment_method'
            ])
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('customer_id', $this->customer->id)
            ->assertJsonPath('confirmation_code', function ($code) {
                return preg_match('/^[A-Z0-9]{6}$/', $code) === 1;
            })
            ->assertJsonPath('special_instructions', 'Entregar apenas para o responsável')
            ->assertJsonPath('payment_method', 'pix');
            
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
        
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
                'errors' => [
                    'estimated_weight' => ['The estimated weight field must be at least 0.'],
                    'type' => ['The type field is required.']
                ]
            ]);

        // Test out-of-coverage delivery
        $outOfCoverageData = $baseData;
        $outOfCoverageData['destination']['lat'] = -22.0000;
        $outOfCoverageData['destination']['lng'] = -43.0000;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', $outOfCoverageData);
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
                'errors' => [
                    'type' => ['The type field is required.']
                ]
            ]);

        // Test invalid payment method
        $invalidPaymentData = $baseData;
        $invalidPaymentData['payment_method'] = 'invalid_method';
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->customerToken
        ])->postJson('/api/v1/deliveries', $invalidPaymentData);
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
                'errors' => [
                    'payment_method' => ['The selected payment method is invalid.']
                ]
            ]);
    }
}
