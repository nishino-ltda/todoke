<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MenuTest extends TestCase
{
    // use DatabaseTransactions;
    private User $restaurant;
    private string $restaurantToken;
    private User $cliente;
    private string $clienteToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->artisan('migrate:fresh');

        // Criar restaurante parceiro apenas uma vez
        if (!isset($this->restaurant)) {
            $this->restaurant = new User([
                'id' => 1, // ID fixo para o restaurante
                'tipo' => 'parceiro',
                'email' => 'bistrotech@example.com',
                'name' => 'Bistro Tech',
                'password' => bcrypt('Bistro123'),
                'status' => 'ativo'
            ]);
            $this->restaurant->save();

            // Criar cliente apenas uma vez
            $this->cliente = User::factory()->create(['tipo' => 'cliente']);
            
            // Obter tokens
            $this->restaurantToken = $this->postJson('/api/v1/auth/login', [
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
        $produtos = Product::factory()
            ->count(3)
            ->forRestaurant($this->restaurant->id)
            ->create([
                'status' => 'disponivel'
            ]);

        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(200)
            ->assertJsonCount(3, 'products')
            ->assertJsonStructure([
                'products' => [
                    '*' => ['id', 'name', 'description', 'price', 'category', 'status']
                ]
            ]);
    }

    public function testFiltroPorCategoria()
    {
        Product::factory()->forRestaurant($this->restaurant->id)->create([
            'category' => 'Japonês',
            'status' => 'disponivel'
        ]);

        Product::factory()->forRestaurant($this->restaurant->id)->create([
            'category' => 'Brasileira',
            'status' => 'disponivel'
        ]);

        $response = $this->getJson('/api/v1/products?category=Japonês');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.category', 'Japonês');
    }

    public function testCriacaoDeProduto()
    {
        $produtoData = [
            'name' => 'Novo Produto',
            'description' => 'Descrição do novo produto',
            'price' => 29.90,
            'category' => 'Teste'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->restaurantToken
        ])->postJson('/api/v1/products', $produtoData);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Novo Produto')
            ->assertJsonPath('restaurantId', $this->restaurant->id)
            ->assertJsonPath('status', 'disponivel');

        $this->assertDatabaseHas('products', [
            'name' => 'Novo Produto',
            'restaurantId' => $this->restaurant->id
        ]);
    }

    public function testAtualizacaoDeProduto()
    {
        $produto = Product::factory()->forRestaurant($this->restaurant->id)->create();

        $updateData = [
            'name' => 'name Atualizado',
            'price' => 39.90,
            'status' => 'indisponivel'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->restaurantToken
        ])->putJson("/api/v1/products/{$produto->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'name Atualizado')
            ->assertJsonPath('price', '39.90')
            ->assertJsonPath('status', 'indisponivel');

        $this->assertDatabaseHas('products', [
            'id' => $produto->id,
            'name' => 'name Atualizado',
            'restaurantId' => $this->restaurant->id
        ]);
    }

    public function testApenasProprietarioPodeAtualizarProduto()
    {
        $produto = Product::factory()->forRestaurant($this->restaurant->id)->create();

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
        $produto1 = Product::factory()->forRestaurant($this->restaurant->id)->create([
            'price' => 10.00
        ]);

        $produto2 = Product::factory()->forRestaurant($this->restaurant->id)->create([
            'price' => 20.00
        ]);

        // Garantir que os produtos estão disponíveis
        $produto1->update(['status' => 'disponivel']);
        $produto2->update(['status' => 'disponivel']);

        $pedidoData = [
            'restaurantId' => (string)$this->restaurant->id,
            'totalValue' => 40.00, // (2 * 10) + 20
            'items' => [
                ['productId' => $produto1->id, 'quantity' => 2],
                ['productId' => $produto2->id, 'quantity' => 1]
            ],
            'delivery' => [
                'destination' => [
                    'lat' => -23.5614,
                    'lng' => -46.6559,
                    'address' => 'Rua Augusta, 500'
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
            ->assertJsonPath('totalValue', '40.00') // (2 * 10) + 20
            ->assertJsonPath('status', 'pending');

        $this->assertDatabaseHas('order_items', [
            'product_id' => $produto1->id,
            'quantity' => 2,
            'unitPrice' => 10.00
        ]);
    }
}
