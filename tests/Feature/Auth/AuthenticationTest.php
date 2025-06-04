<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        // 1. API login to get token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);
        $loginResponse->assertOk();
        $token = $loginResponse->json('token');

        // 2. Convert token to session
        $sessionResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/v1/auth/token-to-session');

        $sessionResponse->assertOk();
        
        // 3. Verify session auth works
        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        // 1. API login to get token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123',
        ]);
        $token = $loginResponse->json('token');

        // 2. Convert token to session
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/v1/auth/token-to-session');
        $this->assertAuthenticated();

        // 3. Create a proper Sanctum token for logout
        $sanctumToken = $user->createToken('test-token')->plainTextToken;
        
        // 4. Logout via API
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $sanctumToken
        ])->post('/api/v1/auth/logout');

        $logoutResponse->assertOk();
        
        // Verify logout was successful by checking response
        $logoutResponse->assertJson([
            'message' => 'Logout realizado com sucesso'
        ]);
    }
}
