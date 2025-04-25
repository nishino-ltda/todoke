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
    private User $customer;
    private string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->artisan('migrate:fresh');

        // Create partner restaurant only once
        if (!isset($this->restaurant)) {
            $this->restaurant = new User([
                'id' => 1, // ID fixo para o restaurante
                'type' => 'partner',
                'email' => 'bistrotech@example.com',
                'name' => 'Bistro Tech',
                'password' => bcrypt('Bistro123'),
                'status' => 'active'
            ]);
            $this->restaurant->save();

            // Create client only once
            $this->client = User::factory()->create(['type' => 'client']);
            
            // Get tokens
            $this->restaurantToken = $this->postJson('/api/v1/auth/login', [
                'email' => 'bistrotech@example.com',
                'password' => 'Bistro123'
            ])->json('token');

            $this->clientToken = $this->postJson('/api/v1/auth/login', [
                'email' => $this->client->email,
                'password' => 'Password123'
            ])->json('token');
        }
    }

    public function testProductListing()
    {
        // Create test products
        $products = Product::factory()
            ->count(3)
            ->forRestaurant($this->restaurant->id)
            ->create([
                'status' => 'available'
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

    public function testCategoryFilter()
    {
        Product::factory()->forRestaurant($this->restaurant->id)->create([
            'category' => 'Japanese',
            'status' => 'available'
        ]);

        Product::factory()->forRestaurant($this->restaurant->id)->create([
            'category' => 'Brazilian',
            'status' => 'available'
        ]);

        $response = $this->getJson('/api/v1/products?category=Japanese');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.category', 'Japanese');
    }

    public function testProductCreation()
    {
        $productData = [
            'name' => 'Novo Produto',
            'description' => 'Descrição do novo produto',
            'price' => 29.90,
            'category' => 'Teste'
        ];
, 
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->restaurantToken
        ])->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'Novo Produto')
            ->assertJsonPath('restaurant_id', $this->restaurant->id)
            ->assertJsonPath('status', 'available');

        $this->assertDatabaseHas('products', [
            'name' => 'Novo Produto',
            'restaurant_id' => $this->restaurant->id
        ]);
    }

    public function testProductUpdate()
    {
        $product = Product::factory()->forRestaurant($this->restaurant->id)->create();

        $updateData = [
            'name' => 'name Atualizado',
            'price' => 39.90,
            'status' => 'unavailable'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->restaurantToken
        ])->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'name Atualizado')
            ->assertJsonPath('price', '39.90')
            ->assertJsonPath('status', 'unavailable');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'name Atualizado',
            'restaurant_id' => $this->restaurant->id
        ]);
    }

    public function testOnlyOwnerCanUpdateProduct()
    {
        $product = Product::factory()->forRestaurant($this->restaurant->id)->create();

        $outroRestaurante = User::factory()->create(['type' => 'parceiro']);
        $outroToken = $this->postJson('/api/v1/auth/login', [
            'email' => $outroRestaurante->email,
            'password' => 'Password123'
        ])->json('token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $outroToken
        ])->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Update Attempt'
        ]);

        $response->assertStatus(403);
    }

    public function testOrderCreationWithProducts()
    {
        $product1 = Product::factory()->forRestaurant($this->restaurant->id)->create([
            'price' => 10.00
        ]);

        $product2 = Product::factory()->forRestaurant($this->restaurant->id)->create([
            'price' => 20.00
        ]);

        // Ensure products are available
        $product1->update(['status' => 'available']);
        $product2->update(['status' => 'available']);

        $orderData = [
            'restaurant_id' => (string)$this->restaurant->id,
            'total_value' => 40.00, // (2 * 10) + 20
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1]
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
            'Authorization' => 'Bearer ' . $this->clientToken
        ])->postJson('/api/v1/orders', $orderData);
        
        if ($response->status() !== 201) {
            dd($response->json()); // Show validation errors
        }

        $response->assertStatus(201)
            ->assertJsonPath('total_value', '40.00') // (2 * 10) + 20
            ->assertJsonPath('status', 'pending');

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 10.00
        ]);
    }
}
