<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class UsabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    #[Test]
    public function error_messages_are_clear_and_helpful()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid-email',
            'password' => 'short'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                    'password'
                ]
            ]);
    }

    #[Test]
    public function required_fields_are_properly_validated()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->postJson('/api/v1/deliveries', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'origin',
                'destination', 
                'item_description',
                'estimated_weight',
                'dimensions',
                'type'
            ]);
    }

    #[Test]
    public function api_documentation_is_available()
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertSee('TODOKE API Documentation');
    }

    #[Test]
    public function responses_have_consistent_structure()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/users/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'phone',
                'type',
                'photoUrl',
                'status'
            ]);
    }

    #[Test]
    public function enum_values_are_properly_documented()
    {
        $user = User::factory()->create(['type' => 'customer']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/users/me');

        $response->assertJson([
            'type' => 'customer'
        ]);

        $this->assertContains($user->type, ['customer', 'courier', 'partner', 'admin']);
    }

    #[Test]
    public function pagination_works_and_has_consistent_format()
    {
        $user = User::factory()->create(['type' => 'customer']);
        // Criar entregas associadas a este usuário
        Delivery::factory()->count(15)->create(['customer_id' => $user->id]);
        
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/deliveries?limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'deliveries',
                'total',
                'per_page',
                'current_page'
            ])
            ->assertJsonCount(5, 'deliveries');
    }
}
