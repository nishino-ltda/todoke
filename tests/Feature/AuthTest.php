<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthTest extends TestCase
{
    public function testAuroraSeCadastraEConfiguraSeuPerfil()
    {
        // Dados para registro
        $userData = [
            'nome' => 'Aurora Silva',
            'email' => 'aurora@example.com',
            'telefone' => '11999999999',
            'tipo' => 'motoboy',
            'senha' => 'SenhaSegura123'
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
            $response->assertStatus(400)
                ->assertJsonValidationErrors([$key]);
        }

        // 3. Testar email duplicado
        $response = $this->postJson('/api/v1/auth/register', $userData);
        $response->assertStatus(409);

        // 4. Testar login após cadastro
        $loginData = [
            'email' => $userData['email'],
            'senha' => $userData['senha']
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
        // Criar usuário admin
        $admin = \App\Models\User::factory()->create([
            'tipo' => 'admin',
            'email' => 'admin@todoke.com',
            'senha' => bcrypt('Admin123')
        ]);

        // Criar alguns usuários de teste
        $motoboy = \App\Models\User::factory()->create(['tipo' => 'motoboy']);
        $cliente = \App\Models\User::factory()->create(['tipo' => 'cliente']);
        $parceiro = \App\Models\User::factory()->create(['tipo' => 'parceiro']);

        // Fazer login como admin
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@todoke.com',
            'senha' => 'Admin123'
        ]);
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
        ])->getJson('/api/v1/admin/users?tipo=motoboy');
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tipo', 'motoboy');

        // 3. Testar atualização de status
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/v1/admin/users/{$motoboy->id}/status", [
            'status' => 'inativo'
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'inativo']);

        // 4. Verificar permissões (usuário não-admin não pode acessar)
        $userToken = $this->postJson('/api/v1/auth/login', [
            'email' => $cliente->email,
            'senha' => 'password'
        ])->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken
        ])->getJson('/api/v1/admin/users');
        
        $response->assertStatus(403);
    }
}
