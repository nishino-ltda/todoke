<?php

namespace Tests\Feature;

use Tests\TestCase;

class PartnerTest extends TestCase
{
    
    public function testBistrotechGerenciaEntregas(): void
    {
        // Criar usuário parceiro
        $parceiro = \App\Models\User::factory()->create([
            'tipo' => 'parceiro',
            'email' => 'bistrotech@example.com',
            'password' => bcrypt('Parceiro123')
        ]);

        // Fazer login
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'bistrotech@example.com',
            'password' => 'Parceiro123'
        ])->json('token');

        // 1. Testar criação de entrega
        $entregaData = [
            'origem' => [
                'lat' => -23.5505,
                'lng' => -46.6333,
                'endereco' => 'Av. Paulista, 1000'
            ],
            'destino' => [
                'lat' => -23.5614,
                'lng' => -46.6559,
                'endereco' => 'Rua Augusta, 500'
            ],
            'descricaoItem' => 'Comida',
            'pesoEstimado' => 1.5,
            'dimensoes' => [
                'largura' => 30,
                'altura' => 20,
                'profundidade' => 20
            ],
            'tipo' => 'normal',
            'cliente' => [
                'nome' => 'Cliente Teste',
                'telefone' => '11999999999'
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/deliveries', $entregaData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'valor', 'tempoEstimado'
            ]);

        $entregaId = $response->json('id');

        // 2. Testar alocação automática vs manual
        // Criar entregador
        $entregador = \App\Models\User::factory()->create(['tipo' => 'entregador']);
        
        // Alocação manual
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/v1/deliveries/{$entregaId}/assign", [
            'entregador_id' => $entregador->id
        ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('deliveries', [
            'id' => $entregaId,
            'entregador_id' => $entregador->id
        ]);

        // 3. Testar upload de planilha (simulado)
        $planilha = [
            'entregas' => [
                [
                    'destino' => ['lat' => -23.5632, 'lng' => -46.6543, 'endereco' => 'Rua Augusta, 300'],
                    'cliente' => ['nome' => 'Cliente 1', 'telefone' => '11999999991']
                ],
                [
                    'destino' => ['lat' => -23.5643, 'lng' => -46.6532, 'endereco' => 'Rua Augusta, 400'],
                    'cliente' => ['nome' => 'Cliente 2', 'telefone' => '11999999992']
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'multipart/form-data'
        ])->postJson('/api/v1/deliveries/batch', $planilha);
        
        $response->assertStatus(201)
            ->assertJsonCount(2, 'entregas');

        // 4. Testar visualização no mapa
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/v1/deliveries?view=mapa');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'entregas' => [['id', 'status', 'origem', 'destino']]
            ]);
    }

    
    public function testLogismasterGerenciaRegioesENodes(): void
    {
        // Criar usuário parceiro logístico
        $parceiro = \App\Models\User::factory()->create([
            'tipo' => 'parceiro',
            'email' => 'logismaster@example.com',
            'password' => bcrypt('Logistica123')
        ]);

        // Fazer login
        $token = $this->postJson('/api/v1/auth/login', [
            'email' => 'logismaster@example.com',
            'password' => 'Logistica123'
        ])->json('token');

        // 1. Testar criação de região
        $poligono = [
            ['lat' => -23.5505, 'lng' => -46.6333],
            ['lat' => -23.5614, 'lng' => -46.6333],
            ['lat' => -23.5614, 'lng' => -46.6559],
            ['lat' => -23.5505, 'lng' => -46.6559]
        ];

        $regiaoData = [
            'nome' => 'Centro de São Paulo',
            'poligono' => $poligono
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/regions', $regiaoData);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'nome', 'status']);
        
        $regiaoId = $response->json('id');

        // 2. Testar validação de polígono
        $invalidPoligono = [
            ['lat' => -23.5505, 'lng' => -46.6333],
            ['lat' => -23.5614, 'lng' => -46.6333] // Polígono inválido (apenas 2 pontos)
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/regions', [
            'nome' => 'Região Inválida',
            'poligono' => $invalidPoligono
        ]);
        
        $response->assertStatus(400)
            ->assertJsonValidationErrors(['poligono']);

        // 3. Testar criação de node
        $nodeData = [
            'tipo' => 'entregador',
            'identificador' => 'MOTO-001',
            'capacidade' => 5,
            'regiaoId' => $regiaoId
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/v1/nodes', $nodeData);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'tipo', 'identificador', 'status']);

        $nodeId = $response->json('id');

        // 4. Testar associação a região
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson("/api/v1/regions/{$regiaoId}");
        
        $response->assertStatus(200)
            ->assertJsonPath('nodesCount', 1);

        // 5. Testar atualização de status do node
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/v1/nodes/{$nodeId}/status", [
            'status' => 'ocupado'
        ]);
        
        $response->assertStatus(200)
            ->assertJson(['status' => 'ocupado']);
    }
}
