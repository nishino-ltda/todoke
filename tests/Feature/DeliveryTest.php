<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $cliente;
    private string $clienteToken;
    private User $entregador; 
    private string $entregadorToken;

    // Set up the test environment
    protected function setUp(): void
    {
        parent::setUp();

        // Create a client user
        $this->cliente = User::factory()->create([
            'id' => 1,
            'tipo' => 'cliente',
            'email' => 'cliente@example.com',
            'password' => Hash::make('Senha123')
        ]);

        // Create a delivery person user
        $this->entregador = User::factory()->entregador()->create([
            'id' => 2,
            'name' => 'Entregador Teste'
        ]);

        // Authenticate the client and retrieve the token
        $clienteLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'cliente@example.com',
            'password' => 'Senha123'
        ]);
        $this->clienteToken = $clienteLogin->json('token');

        // Authenticate the delivery person and retrieve the token
        $entregadorLogin = $this->postJson('/api/v1/auth/login', [
            'email' => $this->entregador->email,
            'password' => 'Senha123'
        ]);
        $this->entregadorToken = $entregadorLogin->json('token');
    }

    // Test the creation of a delivery
    public function testCriacaoDeEntrega()
    {
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

        // Test normal delivery creation
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
            });
            
        $this->assertDatabaseHas('deliveries', [
            'clienteId' => $this->cliente->id,
            'status' => 'pendente'
        ]);

        $normalValue = $response->json('valor');
        $normalTime = $response->json('tempoEstimado');

        // Test express delivery creation
        $expressData = array_merge($baseData, ['tipo' => 'expressa']);
        $expressResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $expressData);
        
        $expressResponse->assertStatus(201);
        $this->assertGreaterThan($normalValue, $expressResponse->json('valor'));
        $this->assertLessThan($normalTime, $expressResponse->json('tempoEstimado'));

        // Test invalid delivery data
        $invalidData = $baseData;
        $invalidData['pesoEstimado'] = -1;
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $invalidData);
        
        $response->assertStatus(400)
            ->assertJson([
                'pesoEstimado' => ['The peso estimado field must be at least 0.']
            ]);

        // Test out-of-coverage delivery
        $outOfCoverageData = $baseData;
        $outOfCoverageData['destino']['lat'] = -22.0000;
        $outOfCoverageData['destino']['lng'] = -43.0000;
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/deliveries', $outOfCoverageData);
        $response->assertStatus(400);
    }

    // Test acceptance and completion of a delivery
    public function testAceitacaoEConclusaoDeEntrega()
    {
        // Create a delivery
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

        $this->assertDatabaseHas('deliveries', [
            'id' => $entrega['id'],
            'status' => 'pendente',
            'clienteId' => $this->cliente->id
        ]);

        // Accept the delivery
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/accept");

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'status', 'entregador'])
            ->assertJsonPath('entregador.id', (string)$this->entregador->id);
            
        $this->assertDatabaseHas('deliveries', [
            'id' => $entrega['id'],
            'entregadorId' => (string)$this->entregador->id,
            'status' => 'aceito'
        ]);

        // Update delivery status to 'em_transporte' and 'entregue'
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

        // Complete the delivery with the correct confirmation code
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'entregue',
            'codigoConfirmacao' => $entrega['codigoConfirmacao'],
            'posicaoAtual' => ['lat' => -23.5614, 'lng' => -46.6559]
        ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('status', 'entregue');

        // Attempt to complete the delivery with an incorrect confirmation code
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'entregue',
            'codigoConfirmacao' => 'CODIGO_INCORRETO'
        ]);
        
        $response->assertStatus(400);
    }

    // Test tracking of a delivery
    public function testAcompanhamentoDeEntrega()
    {
        // Create a delivery
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

        // Accept the delivery
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/accept");

        // Check delivery details
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

        // Update delivery position
        $novaPosicao = ['lat' => -23.5555, 'lng' => -46.6444];
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'em_transporte',
            'posicaoAtual' => $novaPosicao
        ]);

        // Verify updated position
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->getJson("/api/v1/deliveries/{$entrega['id']}");
        
        $response->assertJsonPath('posicaoAtual', $novaPosicao)
            ->assertJsonPath('status', 'em_transporte');

        // Complete the delivery
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->patchJson("/api/v1/deliveries/{$entrega['id']}/status", [
            'status' => 'entregue',
            'codigoConfirmacao' => $entrega['codigoConfirmacao']
        ]);

        // Check notifications for the client
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->getJson("/api/v1/notifications");
        
        $response->assertStatus(200)
            ->assertJsonFragment([
                'tipo' => 'entrega_atualizada',
                'entrega_id' => $entrega['id'],
                'status' => 'entregue'
            ]);

        // Send a message from the client
        $mensagem = ['texto' => 'Onde você está?'];
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson("/api/v1/deliveries/{$entrega['id']}/messages", $mensagem);
        
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'texto', 'autor']);

        // Retrieve messages as the delivery person
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->entregadorToken
        ])->getJson("/api/v1/deliveries/{$entrega['id']}/messages");
        
        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment($mensagem);
    }
}
