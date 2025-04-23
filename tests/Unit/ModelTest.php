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
            'nome' => 'Test User',
            'email' => 'test@example.com',
            'tipo' => 'cliente',
            'status' => 'ativo'
        ]);

        $this->assertEquals('Test User', $user->nome);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('cliente', $user->tipo);
        $this->assertEquals('ativo', $user->status);
    }

    /** @test */
    public function product_model_has_expected_attributes()
    {
        $product = Product::factory()->create([
            'nome' => 'Test Product',
            'preco' => 19.99,
            'status' => 'disponivel'
        ]);

        $this->assertEquals('Test Product', $product->nome);
        $this->assertEquals(19.99, $product->preco);
        $this->assertEquals('disponivel', $product->status);
    }

    /** @test */
    public function order_model_has_expected_attributes()
    {
        $order = Order::factory()->create([
            'status' => 'em_preparo',
            'valorTotal' => 59.90
        ]);

        $this->assertEquals('em_preparo', $order->status);
        $this->assertEquals(59.90, $order->valorTotal);
    }

    /** @test */
    public function delivery_model_has_expected_attributes()
    {
        $delivery = Delivery::factory()->create([
            'status' => 'pendente',
            'tipo' => 'documento'
        ]);

        $this->assertEquals('pendente', $delivery->status);
        $this->assertEquals('documento', $delivery->tipo);
    }

    /** @test */
    public function node_model_has_expected_attributes()
    {
        $node = Node::factory()->create([
            'tipo' => 'restaurante',
            'status' => 'ativo'
        ]);

        $this->assertEquals('restaurante', $node->tipo);
        $this->assertEquals('ativo', $node->status);
    }

    /** @test */
    public function region_model_has_expected_attributes()
    {
        $region = Region::factory()->create([
            'nome' => 'Zona Norte',
            'status' => 'ativo'
        ]);

        $this->assertEquals('Zona Norte', $region->nome);
        $this->assertEquals('ativo', $region->status);
    }

    /** @test */
    public function rating_model_has_expected_attributes()
    {
        $rating = Rating::factory()->create([
            'nota' => 5,
            'comentario' => 'Excelente serviço!'
        ]);

        $this->assertEquals(5, $rating->nota);
        $this->assertEquals('Excelente serviço!', $rating->comentario);
    }

    /** @test */
    public function user_has_many_deliveries()
    {
        $user = User::factory()->create();
        $deliveries = Delivery::factory()->count(3)->create(['clienteId' => $user->id]);

        $this->assertCount(3, $user->deliveriesAsClient);
    }

    /** @test */
    public function order_has_many_products()
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        
        $order->itens()->create([
            'product_id' => $product->id,
            'quantidade' => 2,
            'precoUnitario' => $product->preco
        ]);

        $this->assertCount(1, $order->itens);
    }
}
