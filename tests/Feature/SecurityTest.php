<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/users/me');
        $response->assertStatus(401);
    }

    /** @test */
    public function users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['tipo' => 'cliente']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['tipo' => 'admin']);
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/admin/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function invalid_json_input_is_rejected()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Content-Type' => 'application/json'
        ])->postJson('/api/v1/deliveries', [
            'origem' => ["lat" => "invalid", "lng" => "invalid"], // Tipos inválidos
            'destino' => null, // Valor nulo não permitido
            'descricaoItem' => 123, // Tipo numérico inválido
            'pesoEstimado' => "heavy", // String inválida
            'dimensoes' => "invalid" // Formato inválido
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function sql_injection_attempts_are_blocked()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => "admin' OR '1'='1",
            'senha' => "anything"
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function rate_limiting_works_on_auth_endpoints()
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'senha' => 'wrongpassword'
            ]);
        }

        $response->assertStatus(429);
    }

    /** @test */
    public function sensitive_data_is_not_exposed_in_responses()
    {
        $user = User::factory()->create([
            'senha' => bcrypt('secret'),
            'remember_token' => 'test-token'
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/users/me');

        $response->assertJsonMissing([
            'senha',
            'remember_token'
        ]);
    }
}
