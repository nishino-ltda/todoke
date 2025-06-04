<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        // Register via API
        $apiResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '(11) 99999-9999',
            'cpf' => '111.222.333-09',
            'type' => 'customer'
        ]);

        $apiResponse->assertStatus(201);
        $token = $apiResponse->json('token');

        // Convert token to web session
        $sessionResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/auth/token-to-session');

        $sessionResponse->assertOk();

        // Verify authentication and redirect
        $this->assertAuthenticated();
        $response = $this->get('/customer/dashboard');
        $response->assertOk();
    }
}
