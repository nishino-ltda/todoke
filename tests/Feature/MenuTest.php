<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MenuTest extends TestCase
{
    use RefreshDatabase;
    private User $restaurante;
    private string $restauranteToken;
    private User $cliente;
    private string $clienteToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar restaurante parceiro apenas uma vez
        if (!isset($this->restaurante)) {
            $this->restaurante = User::factory()->create([
                'tipo' => 'parceiro',
                'email' => 'bistrotech@example.com',
                'password' => bcrypt('Bistro123'),
                'uuid' => \Illuminate\Support\Str::uuid()
            ]);

            // Criar cliente apenas uma vez
            $this->cliente = User::factory()->create(['tipo' => 'cliente']);
            
            // Obter tokens
            $this->restauranteToken = $this->postJson('/api/v1/auth/login', [
                'email' => 'bistrotech@example.com',
                'password' => 'Bistro123'
            ])->json('token');

            $this->clienteToken = $this->postJson('/api/v1/auth/login', [
                'email' => $this->cliente->email,
                'password' => 'Password123'
            ])->json('token');
        }
    }

    public function testListagemDeProdutos()
    {
        // Criar produtos de teste
        $produtos = Product::factory()->count(3)->create([
            'restauranteId' => $this->restaurante->id,
            'status' => 'disponivel'
        ]);

        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'produtos')
            ->assertJsonStructure([
                'produtos' => [
                    '*' => ['id', 'name', 'descricao', 'preco', 'categoria', 'status']
                ]
            ]);
    }

    public function testFiltroPorCategoria()
    {
        Product::factory()->create([
            'restauranteId' => $this->restaurante->id,
            'categoria' => 'Japonês',
            'status' => 'disponivel'
        ]);

        Product::factory()->create([
            'restauranteId' => $this->restaurante->id,
            'categoria' => 'Brasileira',
            'status' => 'disponivel'
        ]);

        $response = $this->getJson('/api/v1/products?categoria=Japonês');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'produtos')
            ->assertJsonPath('produtos.0.categoria', 'Japonês');
    }

    public function testCriacaoDeProduto()
    {
        $produtoData = [
            'name' => 'Novo Produto',
            'descricao' => 'Descrição do novo produto',
            'preco' => 29.90,
            'categoria' => 'Teste'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->restauranteToken
        ])->postJson('/api/v1/products', $produtoData);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Novo Produto')
            ->assertJsonPath('restauranteId', $this->restaurante->id)
            ->assertJsonPath('status', 'disponivel');

        $this->assertDatabaseHas('products', [
            'name' => 'Novo Produto',
            'restauranteId' => $this->restaurante->id
        ]);
    }

    public function testAtualizacaoDeProduto()
    {
        $produto = Product::factory()->create([
            'restauranteId' => $this->restaurante->id
        ]);

        $updateData = [
            'name' => 'name Atualizado',
            'preco' => 39.90,
            'status' => 'indisponivel'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->restauranteToken
        ])->putJson("/api/v1/products/{$produto->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'name Atualizado')
            ->assertJsonPath('preco', 39.90)
            ->assertJsonPath('status', 'indisponivel');

        $this->assertDatabaseHas('products', [
            'id' => $produto->id,
            'name' => 'name Atualizado',
            'restauranteId' => $this->restaurante->id
        ]);
    }

    public function testApenasProprietarioPodeAtualizarProduto()
    {
        $produto = Product::factory()->create([
            'restauranteId' => $this->restaurante->id
        ]);

        $outroRestaurante = User::factory()->create(['tipo' => 'parceiro']);
        $outroToken = $this->postJson('/api/v1/auth/login', [
            'email' => $outroRestaurante->email,
            'password' => 'Password123'
        ])->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $outroToken
        ])->putJson("/api/v1/products/{$produto->id}", [
            'name' => 'Tentativa de Alteração'
        ]);

        $response->assertStatus(403);
    }

    public function testCriacaoDePedidoComProdutos()
    {
        $produto1 = Product::factory()->create([
            'restauranteId' => $this->restaurante->uuid,
            'preco' => 10.00
        ]);

        $produto2 = Product::factory()->create([
            'restauranteId' => $this->restaurante->uuid,
            'preco' => 20.00
        ]);

        $pedidoData = [
            'restauranteId' => $this->restaurante->uuid,
            'itens' => [
                ['produtoId' => $produto1->id, 'quantidade' => 2],
                ['produtoId' => $produto2->id, 'quantidade' => 1]
            ],
            'entrega' => [
                'destino' => [
                    'lat' => -23.5614,
                    'lng' => -46.6559,
                    'endereco' => 'Rua Augusta, 500'
                ]
            ]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->clienteToken
        ])->postJson('/api/v1/orders', $pedidoData);
        
        if ($response->status() !== 201) {
            dd($response->json()); // Mostrar erros de validação
        }

        $response->assertStatus(201)
            ->assertJsonPath('valorTotal', 40.00) // (2 * 10) + 20
            ->assertJsonPath('status', 'em_analise');

        $this->assertDatabaseHas('order_items', [
            'product_id' => $produto1->id,
            'quantidade' => 2,
            'precoUnitario' => 10.00
        ]);
    }
}
