<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\Node;
use App\Models\Region;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function user_model_has_expected_attributes()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'type' => 'customer',
            'status' => 'active'
        ]);

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('customer', $user->type);
        $this->assertEquals('active', $user->status);
    }

    /** @test */
    public function product_model_has_expected_attributes()
    {
        $restaurante = User::factory()->create(['type' => 'partner']);
        
        $product = Product::factory()
            ->forRestaurant($restaurante->id)
            ->create([
                'name' => 'Test Product',
                'price' => 19.99,
                'status' => 'available'
            ]);

        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(19.99, $product->price);
        $this->assertEquals('available', $product->status);
        $this->assertEquals($restaurante->id, $product->restaurant_id);
    }

    /** @test */
    public function order_model_has_expected_attributes()
    {
        $order = Order::factory()->create([
            'status' => 'preparing',
            'total_value' => 59.90
        ]);

        $this->assertEquals('preparing', $order->status);
        $this->assertEquals(59.90, $order->total_value);
    }

    /** @test */
    public function delivery_model_has_expected_attributes()
    {
        $delivery = Delivery::factory()
            ->state([
                'status' => 'pending',
                'type' => 'standard'
            ])
            ->create();

        $this->assertEquals('pending', $delivery->status);
        $this->assertEquals('standard', $delivery->type);
    }

    /** @test */
    public function node_model_has_expected_attributes()
    {
        $node = Node::factory()->create([
            'type' => 'restaurant',
            'status' => 'active'
        ]);

        $this->assertEquals('restaurant', $node->type);
        $this->assertEquals('active', $node->status);
    }

    /** @test */
    public function region_model_has_expected_attributes()
    {
        $region = Region::factory()->create([
            'name' => 'Zona Norte',
            'status' => 'active'
        ]);

        $this->assertEquals('Zona Norte', $region->name);
        $this->assertEquals('active', $region->status);
    }

    /** @test */
    public function rating_model_has_expected_attributes()
    {
        $rating = Rating::factory()->create([
            'rating' => 5,
            'comment' => 'Excelente serviço!'
        ]);

        $this->assertEquals(5, $rating->rating);
        $this->assertEquals('Excelente serviço!', $rating->comment);
    }

    /** @test */
    public function user_has_many_deliveries()
    {
        $user = User::factory()->create();
        $deliveries = Delivery::factory()->count(3)->create(['customer_id' => $user->id]);

        $this->assertCount(3, $user->deliveriesAsClient);
    }

    /** @test */
    public function order_has_many_products()
    {
        $restaurant = User::factory()->create(['type' => 'partner']);
        $order = Order::factory()->create(['restaurant_id' => $restaurant->id]);
        $product = Product::factory()
            ->forRestaurant($restaurant->id)
            ->create();
        
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => $product->price
        ]);

        $this->assertCount(1, $order->items);
    }
}
