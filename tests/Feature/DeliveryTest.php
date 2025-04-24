<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Support\Facades\Hash;
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

        // Criar usuários de teste com IDs explícitos para evitar conflitos
        $this->cliente = User::factory()->create([
            'id' => 1,
            'tipo' => 'cliente',
            'email' => 'cliente@example.com',
            'password' => Hash::make('Senha123')
        ]);

        $this->entregador = User::factory()->entregador()->create([
            'id' => 2,
            'name' => 'Entregador Teste'
        ]);
        
        echo "\n--- Dados do entregador após criação ---";
        echo "\nID: " . $this->entregador->id;
        echo "\nname: " . $this->entregador->name;
        echo "\nTipo: " . $this->entregador->tipo;
        echo "\nEmail: " . $this->entregador->email;
        
        echo "\nTipo do entregador após criação: " . $this->entregador->tipo;
        echo "\nTipo do entregador após refresh: " . $this->entregador->fresh()->tipo;

        // Obter tokens
        $clienteLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'cliente@example.com',
            'password' => 'Senha123'
        ]);
        $this->clienteToken = $clienteLogin->json('token');
        echo "\nCliente ID: " . $this->cliente->id;
        echo "\nCliente Token: " . $this->clienteToken;

        echo "\n--- Tentando login do entregador ---";
        $entregadorLogin = $this->postJson('/api/v1/auth/login', [
            'email' => $this->entregador->email,
            'password' => 'Senha123'
        ]);
        
        echo "\nStatus do login: " . $entregadorLogin->status();
        echo "\nResposta do login: " . $entregadorLogin->content();
        $this->entregadorToken = $entregadorLogin->json('token');
        $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($this->entregadorToken)->tokenable;
        
        echo "\nEntregador ID: " . $this->entregador->id;
        echo "\nEntregador Token: " . $this->entregadorToken;
        echo "\nTipo do usuário do token: " . $userFromToken->tipo;
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
            ->assertJsonPath('codigoConfirmacao', function ($code) {
                return preg_match('/^[A-Z0-9]{6}$/', $code) === 1;
            }); // Verifica se o código tem 6 caracteres alfanuméricos
            
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
        // Test invalid pesoEstimado value instead of missing field
        // Test invalid pesoEstimado value (negative)
        $invalidData = $baseData;
        $invalidData['pesoEstimado'] = -1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $invalidData);
        
        $response->assertStatus(400)
            ->assertJson([
                'pesoEstimado' => ['The peso estimado field must be at least 0.']
            ]);

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
        echo "\n--- Teste de aceitação ---";
        echo "\nID do entregador: " . $this->entregador->id;
        echo "\nToken do entregador: " . $this->entregadorToken;
        echo "\nID da entrega: " . $entrega['id'];
        
        // Debug antes da requisição
        echo "\nVerificando usuário do token:";
        $userFromToken = \Laravel\Sanctum\PersonalAccessToken::findToken($this->entregadorToken)->tokenable;
        echo "\nID: " . $userFromToken->id;
        echo "\nTipo: " . $userFromToken->tipo;
        echo "\nname: " . $userFromToken->name;

        // Debug adicional para verificar o token
        echo "\nVerificando token antes da requisição:";
        $tokenParts = explode('|', $this->entregadorToken);
        $tokenId = $tokenParts[0];
        $token = \Laravel\Sanctum\PersonalAccessToken::find($tokenId);
        echo "\nToken ID: " . $tokenId;
        echo "\nToken User ID: " . $token->tokenable_id;
        echo "\nToken Abilities: " . json_encode($token->abilities);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/accept");

        // Debug após a requisição
        echo "\nStatus code: " . $response->status();
        echo "\nResponse: " . $response->content();
        
        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'status', 'entregador'])
            ->assertJsonPath('entregador.id', (string)$this->entregador->id);
            
        $this->assertDatabaseHas('deliveries', [
            'id' => $entrega['id'],
            'entregadorId' => (string)$this->entregador->id,
            'status' => 'aceito'
        ]);

        // 2. Testar atualização de status
        $statuses = ['em_transporte', 'entregue'];
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
                'entregadorId' => (string)$this->entregador->id
            ]);
        }

        // 3. Testar confirmação com código
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'entregue',
            'codigoConfirmacao' => $entrega['codigoConfirmacao'],
            'posicaoAtual' => ['lat' => -23.5614, 'lng' => -46.6559]
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
            ->assertJsonPath('entregador.id', (string)$this->entregador->id)
            ->assertJsonPath('entregador.name', $this->entregador->name);

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
