<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UsabilityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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

    /** @test */
    public function required_fields_are_properly_validated()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->postJson('/api/v1/deliveries', []);

        $response->assertStatus(400)
            ->assertJsonValidationErrors([
                'origin',
                'destination', 
                'item_description',
                'estimated_weight',
                'dimensions',
                'type'
            ]);
    }

    /** @test */
    public function api_documentation_is_available()
    {
        $response = $this->get('/api/documentation');

        $response->assertStatus(200)
            ->assertSee('TODOKE API Documentation');
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function pagination_works_and_has_consistent_format()
    {
        Delivery::factory()->count(15)->create();
        $user = User::factory()->create();
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
