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
            'restauranteId' => 'required|uuid|exists:users,id',
            'itens' => 'required|array|min:1',
            'itens.*.produtoId' => [
                'required',
                'uuid',
                Rule::exists('products', 'id')->where(function ($query) use ($request) {
                    $query->where('restauranteId', $request->restauranteId);
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
            'clienteId' => $request->user()->id,
            'restauranteId' => $request->restauranteId,
            'status' => 'pendente',
            'entrega' => $request->entrega
        ]);

        foreach ($request->itens as $item) {
            $product = Product::find($item['produtoId']);
            
            OrderItem::create([
                'pedidoId' => $order->id,
                'produtoId' => $item['produtoId'],
                'quantidade' => $item['quantidade'],
                'precoUnitario' => $product->preco
            ]);
        }

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'total' => $order->items()->sum('precoUnitario')
        ], 201);
    }
}
