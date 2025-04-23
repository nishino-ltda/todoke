<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $cliente;
    private string $clienteToken;
    private User $entregador; 
    private string $entregadorToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuários de teste
        $this->cliente = User::factory()->create([
            'tipo' => 'cliente',
            'email' => 'cliente@example.com',
            'senha' => 'password'
        ]);

        $this->entregador = User::factory()->create([
            'tipo' => 'entregador',
            'email' => 'entregador@example.com',
            'senha' => 'password'
        ]);

        // Obter tokens
        $this->clienteToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'cliente@example.com',
            'senha' => 'password'
        ])->json('token');

        $this->entregadorToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'entregador@example.com',
            'senha' => 'password'
        ])->json('token');
    }
    public function testCriacaoDeEntrega()
    {
        // Dados básicos da entrega
        $baseData = [
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
            'descricaoItem' => 'Documentos importantes',
            'pesoEstimado' => 0.5,
            'dimensoes' => [
                'largura' => 25,
                'altura' => 5,
                'profundidade' => 15
            ]
        ];

        // 1. Testar criação de entrega normal
        $normalData = array_merge($baseData, ['tipo' => 'normal']);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $normalData);
        
        $response->assertStatus(201)
            ->assertJsonStructure([
                'id', 'valor', 'tempoEstimado', 'codigoConfirmacao', 'status'
            ])
            ->assertJsonPath('status', 'pendente')
            ->assertJsonPath('clienteId', $this->cliente->id)
            ->assertJsonCount(6, 'codigoConfirmacao'); // Verifica se o código tem 6 caracteres
            
        $this->assertDatabaseHas('deliveries', [
            'clienteId' => $this->cliente->id,
            'status' => 'pendente'
        ]);

        // 2. Verificar cálculo automático
        $normalValue = $response->json('valor');
        $normalTime = $response->json('tempoEstimado');

        // 3. Testar tipos de entrega
        $expressData = array_merge($baseData, ['tipo' => 'expressa']);
        $expressResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $expressData);
        
        $expressResponse->assertStatus(201);
        $this->assertGreaterThan($normalValue, $expressResponse->json('valor'));
        $this->assertLessThan($normalTime, $expressResponse->json('tempoEstimado'));

        // 4. Testar validações
        $invalidData = $baseData;
        unset($invalidData['pesoEstimado']);
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $invalidData);
        $response->assertStatus(400)
            ->assertJsonValidationErrors(['pesoEstimado']);

        // 5. Testar área fora de cobertura
        $outOfCoverageData = $baseData;
        $outOfCoverageData['destino']['lat'] = -22.0000; // Local distante
        $outOfCoverageData['destino']['lng'] = -43.0000;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $outOfCoverageData);
        $response->assertStatus(400);
    }

    public function testAceitacaoEConclusaoDeEntrega()
    {
        // Cliente cria uma entrega
        $entrega = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', [
            'origem' => ['lat' => -23.5505, 'lng' => -46.6333, 'endereco' => 'Av. Paulista, 1000'],
            'destino' => ['lat' => -23.5614, 'lng' => -46.6559, 'endereco' => 'Rua Augusta, 500'],
            'descricaoItem' => 'Documentos importantes',
            'pesoEstimado' => 0.5,
            'dimensoes' => ['largura' => 25, 'altura' => 5, 'profundidade' => 15],
            'tipo' => 'normal'
        ])->json();

        // Verificar se a entrega foi criada corretamente
        $this->assertDatabaseHas('deliveries', [
            'id' => $entrega['id'],
            'status' => 'pendente',
            'clienteId' => $this->cliente->id
        ]);

        // 1. Testar aceite de entrega
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/accept");
        
        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'status', 'entregador']);
            
        $this->assertDatabaseHas('deliveries', [
            'id' => $entrega['id'],
            'entregadorId' => $this->entregador->id,
            'status' => 'aceito'
        ]);

        // 2. Testar atualização de status
        $statuses = ['aceito', 'em_transporte', 'entregue'];
        foreach ($statuses as $status) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->entregadorToken
            ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
                'status' => $status,
                'posicaoAtual' => [
                    'lat' => -23.5500 + (rand(0, 100)/1000),
                    'lng' => -46.6300 + (rand(0, 100)/1000)
                ]
            ]);
            
            $response->assertStatus(200)
                ->assertJsonPath('status', $status);
                
            $this->assertDatabaseHas('deliveries', [
                'id' => $entrega['id'],
                'status' => $status,
                'entregadorId' => $this->entregador->id
            ]);
        }

        // 3. Testar confirmação com código
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'entregue',
            'codigoConfirmacao' => $entrega['codigoConfirmacao'],
            'posicaoAtual' => $entrega['destino']
        ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('status', 'entregue');

        // 4. Testar código incorreto (deve falhar)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'entregue',
            'codigoConfirmacao' => 'CODIGO_INCORRETO'
        ]);
        
        $response->assertStatus(400);
    }

    public function testAcompanhamentoDeEntrega()
    {
        // Cliente cria uma entrega
        $entrega = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', [
            'origem' => ['lat' => -23.5505, 'lng' => -46.6333, 'endereco' => 'Av. Paulista, 1000'],
            'destino' => ['lat' => -23.5614, 'lng' => -46.6559, 'endereco' => 'Rua Augusta, 500'],
            'descricaoItem' => 'Documentos importantes',
            'pesoEstimado' => 0.5,
            'dimensoes' => ['largura' => 25, 'altura' => 5, 'profundidade' => 15],
            'tipo' => 'normal'
        ])->json();

        // Entregador aceita a entrega
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/accept");

        // 1. Testar endpoint de acompanhamento
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->getJson("/api/v1/deliveries/{$entrega['id']}");
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'status', 'entregador', 'posicaoAtual', 'historicoStatus'
            ])
            ->assertJsonPath('status', 'aceito')
            ->assertJsonPath('entregador.id', $this->entregador->id);

        // 2. Verificar atualização em tempo real
        $novaPosicao = ['lat' => -23.5555, 'lng' => -46.6444];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'em_transporte',
            'posicaoAtual' => $novaPosicao
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->getJson("/api/v1/deliveries/{$entrega['id']}");
        
        $response->assertJsonPath('posicaoAtual', $novaPosicao)
            ->assertJsonPath('status', 'em_transporte');

        // 3. Testar notificações (simulado)
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'entregue',
            'codigoConfirmacao' => $entrega['codigoConfirmacao']
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->getJson("/api/v1/notifications");
        
        $response->assertStatus(200)
            ->assertJsonFragment([
                'tipo' => 'entrega_atualizada',
                'entrega_id' => $entrega['id'],
                'status' => 'entregue'
            ]);

        // 4. Testar chat (simulado)
        $mensagem = ['texto' => 'Onde você está?'];
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson("/api/v1/deliveries/{$entrega['id']}/messages", $mensagem);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'texto', 'autor']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->getJson("/api/v1/deliveries/{$entrega['id']}/messages");
        
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment($mensagem);
    }
}
