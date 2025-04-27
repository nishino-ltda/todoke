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
    private User $partner;
    private string $partnerToken;
    private User $customer;
    private string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run migrations
        $this->artisan('migrate:fresh');

        // Create partner restaurant only once
        if (!isset($this->partner)) {
            $this->partner = new User([
                 'id' => 1, // ID fixo para o partner
                'type' => 'partner',
                'email' => 'bistrotech@example.com',
                'name' => 'Bistro Tech',
                'password' => bcrypt('Bistro123'),
                'status' => 'active'
            ]);
            $this->partner->save();

            // Create customer only once
            $this->customer = User::factory()->create(['type' => 'customer']);
            
            // Get tokens
            $this->partnerToken = $this->postJson('/api/v1/auth/login', [
                'email' => 'bistrotech@example.com',
                'password' => 'Bistro123'
            ])->json('token');

            $this->customerToken = $this->postJson('/api/v1/auth/login', [
                'email' => $this->customer->email,
                'password' => 'Password123'
            ])->json('token');
        }
    }

    public function testProductListing()
    {
        // Create test products
        $products = Product::factory()
            ->count(3)
            ->forPartner($this->partner->id)
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
        Product::factory()->forPartner($this->partner->id)->create([
            'category' => 'Japanese',
            'status' => 'available'
        ]);

        Product::factory()->forPartner($this->partner->id)->create([
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
            'name' => 'New Product',
            'description' => 'New product description',
            'price' => 29.90,
            'category' => 'Teste'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->partnerToken
        ])->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonPath('name', 'New Product')
            ->assertJsonPath('partner_id', $this->partner->id)
            ->assertJsonPath('status', 'available');

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'partner_id' => $this->partner->id
        ]);
    }

    public function testProductUpdate()
    {
        $product = Product::factory()->forPartner($this->partner->id)->create();

        $updateData = [
            'name' => 'Updated name',
            'price' => 39.90,
            'status' => 'unavailable'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->partnerToken
        ])->putJson("/api/v1/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Updated name')
            ->assertJsonPath('price', '39.90')
            ->assertJsonPath('status', 'unavailable');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated name',
            'partner_id' => $this->partner->id
        ]);
    }

    public function testOnlyOwnerCanUpdateProduct()
    {
        $product = Product::factory()->forPartner($this->partner->id)->create();

         $outroPartner = User::factory()->create(['type' => 'partner']);
        $outroToken = $this->postJson('/api/v1/auth/login', [
             'email' => $outroPartner->email,
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
        $product1 = Product::factory()->forPartner($this->partner->id)->create([
            'price' => 10.00
        ]);

        $product2 = Product::factory()->forPartner($this->partner->id)->create([
            'price' => 20.00
        ]);

        // Ensure products are available
        $product1->update(['status' => 'available']);
        $product2->update(['status' => 'available']);

        $orderData = [
            'partner_id' => (string)$this->partner->id,
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
            'Authorization' => 'Bearer ' . $this->customerToken
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
