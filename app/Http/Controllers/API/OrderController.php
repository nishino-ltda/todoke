<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurantId' => 'required|exists:users,id',
            'itens' => 'required|array|min:1',
            'itens.*.produtoId' => [
                'required',
                Rule::exists('products', 'id')->where(function ($query) use ($request) {
                    $query->where('restaurantId', $request->restaurantId);
                })
            ],
            'itens.*.quantidade' => 'required|integer|min:1',
            'entrega.destino.lat' => 'required|numeric|between:-90,90',
            'entrega.destino.lng' => 'required|numeric|between:-180,180',
            'entrega.destino.endereco' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $order = Order::create([
            'clientId' => $request->user()->id,
            'restaurantId' => $request->restaurantId,
            'status' => 'pending',
            'valorTotal' => 0
        ]);

        $total = 0;
        foreach ($request->itens as $item) {
            $product = Product::find($item['produtoId']);
            $itemTotal = $product->preco * $item['quantidade'];
            $total += $itemTotal;
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['produtoId'],
                'quantidade' => $item['quantidade'],
                'precoUnitario' => $product->preco
            ]);
        }

        $order->update(['valorTotal' => $total]);

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'valorTotal' => number_format($order->valorTotal, 2, '.', '')
        ], 201);
    }
}
