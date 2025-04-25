<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function testAuroraRegistersAndConfiguresProfile()
    {
        // Registration data
        $userData = [
            'name' => 'Aurora Silva',
            'email' => 'aurora@example.com',
            'phone' => '11999999999',
            'type' => 'courrier',
            'password' => 'SenhaSegura123'
        ];

        // 1. Test new courier registration
        $response = $this->postJson('/api/v1/auth/register', $userData);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id', 'name', 'email', 'type'
                ]
            ]);

        // 2. Verify required field validation
        foreach ($userData as $key => $value) {
            $invalidData = $userData;
            unset($invalidData[$key]);
            
            $response = $this->postJson('/api/v1/auth/register', $invalidData);
            $response->assertStatus(422)
                ->assertJsonValidationErrors([$key]);
        }

        // 3. Test duplicate email
        $response = $this->postJson('/api/v1/auth/register', $userData);
        $response->assertStatus(409);

        // 4. Test login after registration
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];
        $response = $this->postJson('/api/v1/auth/login', $loginData);
        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user']);

        $token = $response->json('token');

        // 5. Test profile retrieval
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/users/me');
        
        $response->assertStatus(200)
            ->assertJson([
                'email' => $userData['email'],
                'name' => $userData['name'],
                'type' => $userData['type']
            ]);

        // 6. Test profile update
        $updateData = [
            'name' => 'Aurora Silva Updated',
            'phone' => '11988888888'
        ];
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson('/api/v1/users/me', $updateData);
        
        $response->assertStatus(200)
            ->assertJson($updateData);
    }

    public function testAdminManagesUsers()
    {
        // Create admin user with correct password (using AuthController fields)
        $admin = \App\Models\User::factory()->create([
            'type' => 'admin',
            'email' => 'admin@todoke.com',
            'password' => Hash::make('Admin123')
        ]);

        // Create test users
        $entregador = \App\Models\User::factory()->create(['type' => 'courrier']);
        $customer = \App\Models\User::factory()->create([
            'type' => 'customer',
            'email' => 'customere@test.com',
            'password' => Hash::make('customere123')
        ]);
        // Delete any existing tokens to ensure fresh token with correct abilities
        $customer->tokens()->delete();
        $partner = \App\Models\User::factory()->create(['type' => 'partner']);

        // Verify admin user before login
        $adminUser = User::where('email', 'admin@todoke.com')->first();
        if ($adminUser->type !== 'admin') {
            dd('ERROR: Admin user has incorrect type', $adminUser);
        }

        // Login as admin
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@todoke.com',
            'password' => 'Admin123'
        ]);
        
        if ($loginResponse->status() !== 200) {
            dd('Login failed', $loginResponse->json());
        }
        
        $token = $loginResponse->json('token');

        // 1. Test user listing
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/admin/users');
        
        $response->assertStatus(200)
            ->assertJsonCount(4, 'data'); // admin + 3 created users

        // 2. Test type filters
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/admin/users?type=courrier');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'courrier');

        // 3. Test status update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/v1/admin/users/{$entregador->id}/status", [
            'status' => 'inactive'
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'inactive']);

        // 4. Verify permissions (non-admin user cannot access)
        // Create new client to test permissions
        $novoCliente = \App\Models\User::factory()->create([
            'type' => 'customer',
            'email' => 'novo.cliente@test.com',
            'password' => Hash::make('customere123')
        ]);
        
        // Authenticate directly as client
        $this->actingAs($novoCliente);
        
        // Verify client cannot access admin routes
        $response = $this->getJson('/api/v1/admin/users');
        
        $response->assertStatus(403);
    }
}
