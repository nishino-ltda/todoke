<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Mockery;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock throttle middleware to disable rate limiting during tests
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }
    public function testAuroraRegistersAndConfiguresProfile()
    {
        // Registration data
        $userData = [
            'name' => 'Aurora Silva',
            'email' => 'aurora@example.com',
            'phone' => '11999999999',
            'cpf' => '111.222.333-44',
            'type' => 'courier',
            'password' => 'SenhaSegura123',
            'password_confirmation' => 'SenhaSegura123',
            'license_number' => 'AB123456',
            'vehicle_type' => 'motorcycle',
            'license_file' => UploadedFile::fake()->create('license.jpg', 1000)
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
            
            // Special handling for password confirmation
            if ($key === 'password_confirmation') {
                $invalidData['password'] = 'DifferentPassword123';
            } else {
                unset($invalidData[$key]);
            }
            
            $response = $this->postJson('/api/v1/auth/register', $invalidData);
            $response->assertStatus(422);
            
            // For password_confirmation, check for password error
            $errorKey = $key === 'password_confirmation' ? 'password' : $key;
            $response->assertJsonValidationErrors([$errorKey]);
        }

        // 3. Test duplicate email with unique CPF
        $duplicateData = $userData;
        $duplicateData['cpf'] = '999.888.777-66'; // Different CPF to trigger only email duplicate
        $response = $this->postJson('/api/v1/auth/register', $duplicateData);
        $response->assertStatus(409);

        // Find the newly registered user
        $user = User::where('email', $userData['email'])->first();
        $this->assertNotNull($user, 'Registered user not found.');

        // Generate token directly
        $token = $user->createToken('test-token')->plainTextToken;

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
        $entregador = \App\Models\User::factory()->create(['type' => 'courier']);
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

        // Generate token with admin abilities
        $token = $admin->createToken('admin-token', ['admin'])->plainTextToken;

        // 1. Test user listing
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/admin/users');
        
        $response->assertStatus(200)
            ->assertJsonCount(4, 'data'); // admin + 3 created users

        // 2. Test type filters
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/admin/users?type=courier');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'courier');

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
