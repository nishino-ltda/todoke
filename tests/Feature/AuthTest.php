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
    public function testAuroraSeCadastraEConfiguraSeuPerfil()
    {
        // Dados para registro
        $userData = [
            'nome' => 'Aurora Silva',
            'email' => 'aurora@example.com',
            'telefone' => '11999999999',
            'tipo' => 'entregador',
            'password' => 'SenhaSegura123'
        ];

        // 1. Testar registro de novo entregador
        $response = $this->postJson('/api/v1/auth/register', $userData);
        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'usuario' => [
                    'id', 'nome', 'email', 'tipo'
                ]
            ]);

        // 2. Verificar validação de campos obrigatórios
        foreach ($userData as $key => $value) {
            $invalidData = $userData;
            unset($invalidData[$key]);
            
            $response = $this->postJson('/api/v1/auth/register', $invalidData);
            $response->assertStatus(422)
                ->assertJsonValidationErrors([$key]);
        }

        // 3. Testar email duplicado
        $response = $this->postJson('/api/v1/auth/register', $userData);
        $response->assertStatus(409);

        // 4. Testar login após cadastro
        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];
        $response = $this->postJson('/api/v1/auth/login', $loginData);
        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'usuario']);

        $token = $response->json('token');

        // 5. Testar obtenção de perfil
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/users/me');
        
        $response->assertStatus(200)
            ->assertJson([
                'email' => $userData['email'],
                'nome' => $userData['nome'],
                'tipo' => $userData['tipo']
            ]);

        // 6. Testar atualização de perfil
        $updateData = [
            'nome' => 'Aurora Silva Updated',
            'telefone' => '11988888888'
        ];
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson('/api/v1/users/me', $updateData);
        
        $response->assertStatus(200)
            ->assertJson($updateData);
    }

    public function testAdminsysGerenciaUsuarios()
    {
        // Criar usuário admin com senha correta (usando campos do AuthController)
        $admin = \App\Models\User::factory()->create([
            'tipo' => 'admin',
            'email' => 'admin@todoke.com',
            'password' => Hash::make('Admin123')
        ]);

        // Criar alguns usuários de teste
        $entregador = \App\Models\User::factory()->create(['tipo' => 'entregador']);
        $cliente = \App\Models\User::factory()->create([
            'tipo' => 'cliente',
            'email' => 'cliente@test.com',
            'password' => Hash::make('Cliente123')
        ]);
        // Delete any existing tokens to ensure fresh token with correct abilities
        $cliente->tokens()->delete();
        $parceiro = \App\Models\User::factory()->create(['tipo' => 'parceiro']);

        // Verificar usuário admin antes do login
        $adminUser = User::where('email', 'admin@todoke.com')->first();
        if ($adminUser->tipo !== 'admin') {
            dd('ERRO: Usuário admin não está com tipo correto', $adminUser);
        }

        // Fazer login como admin
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@todoke.com',
            'password' => 'Admin123'
        ]);
        
        if ($loginResponse->status() !== 200) {
            dd('Falha no login', $loginResponse->json());
        }
        
        $token = $loginResponse->json('token');

        // 1. Testar listagem de usuários
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/admin/users');
        
        $response->assertStatus(200)
            ->assertJsonCount(4, 'data'); // admin + 3 usuários criados

        // 2. Testar filtros por tipo
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/admin/users?tipo=entregador');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tipo', 'entregador');

        // 3. Testar atualização de status
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/v1/admin/users/{$entregador->id}/status", [
            'status' => 'inativo'
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'inativo']);

        // 4. Verificar permissões (usuário não-admin não pode acessar)
        // Criar um novo cliente para testar permissões
        $novoCliente = \App\Models\User::factory()->create([
            'tipo' => 'cliente',
            'email' => 'novo.cliente@test.com',
            'password' => Hash::make('Cliente123')
        ]);
        
        // Autenticar diretamente como o cliente
        $this->actingAs($novoCliente);
        
        // Verificar que o cliente não pode acessar rotas de admin
        $response = $this->getJson('/api/v1/admin/users');
        
        $response->assertStatus(403);
    }
}
