<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Addon;
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

    public function testAddonCreation()
    {
        $addonData = [
            'name' => 'Extra Cheese',
            'description' => 'Premium cheddar cheese',
            'price' => 5.00
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->partnerToken
        ])->postJson('/api/v1/addons', $addonData);
        
        $response->assertStatus(201)
            ->assertJsonPath('name', 'Extra Cheese')
            ->assertJsonPath('price', '5.00');
            
        $this->assertDatabaseHas('addons', [
            'name' => 'Extra Cheese',
            'partner_id' => $this->partner->id
        ]);
    }
    
    public function testAssociatingAddonsWithProduct()
    {
        $product = Product::factory()->forPartner($this->partner->id)->create();
        $addon1 = Addon::factory()->forPartner($this->partner->id)->create();
        $addon2 = Addon::factory()->forPartner($this->partner->id)->create();
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->partnerToken
        ])->postJson("/api/v1/products/{$product->id}/addons", [
            'addon_ids' => [$addon1->id, $addon2->id]
        ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('product_addon', [
            'product_id' => $product->id,
            'addon_id' => $addon1->id
        ]);
        
        $this->assertDatabaseHas('product_addon', [
            'product_id' => $product->id,
            'addon_id' => $addon2->id
        ]);
    }
    
    public function testGetProductAddons()
    {
        $product = Product::factory()->forPartner($this->partner->id)->create();
        $addon = Addon::factory()->forPartner($this->partner->id)->create([
            'name' => 'Bacon Bits',
            'price' => 3.50
        ]);
        
        // Associate addon with product
        $product->addons()->attach($addon->id);
        
        $response = $this->getJson("/api/v1/products/{$product->id}/addons");
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'addons')
            ->assertJsonPath('addons.0.name', 'Bacon Bits')
            ->assertJsonPath('addons.0.price', '3.50');
    }
    
    public function testOrderCreationWithAddons()
    {
        $product = Product::factory()->forPartner($this->partner->id)->create([
            'price' => 10.00
        ]);
        
        $addon = Addon::factory()->forPartner($this->partner->id)->create([
            'price' => 2.50
        ]);
        
        // Associate addon with product
        $product->addons()->attach($addon->id);
        
        $orderData = [
            'partner_id' => (string)$this->partner->id,
            'items' => [
                [
                    'product_id' => $product->id, 
                    'quantity' => 1,
                    'addons' => [
                        ['addon_id' => $addon->id, 'quantity' => 2]
                    ]
                ]
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
            ->assertJsonPath('total_value', '15.00'); // 10.00 + (2.50 * 2)
        
        // Check order item has the correct addons
        $orderId = $response->json('id');
        $orderItem = DB::table('order_items')
            ->where('order_id', $orderId)
            ->first();
        
        $this->assertNotNull($orderItem);
        $selectedAddons = json_decode($orderItem->selected_addons, true);
        $this->assertNotNull($selectedAddons);
        $this->assertCount(1, $selectedAddons);
        $this->assertEquals($addon->id, $selectedAddons[0]['id']);
        $this->assertEquals(2, $selectedAddons[0]['quantity']);
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
    
    public function testIncompatibleAddonRejection()
    {
        $product = Product::factory()->forPartner($this->partner->id)->create([
            'price' => 10.00
        ]);
        
        $addon = Addon::factory()->forPartner($this->partner->id)->create([
            'price' => 2.50
        ]);
        
        // Do NOT associate the addon with the product
        
        $orderData = [
            'partner_id' => (string)$this->partner->id,
            'items' => [
                [
                    'product_id' => $product->id, 
                    'quantity' => 1,
                    'addons' => [
                        ['addon_id' => $addon->id, 'quantity' => 1]
                    ]
                ]
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
        
        $response->assertStatus(400)
            ->assertJsonFragment([
                'message' => "Addon {$addon->name} is not compatible with {$product->name}"
            ]);
    }
}
