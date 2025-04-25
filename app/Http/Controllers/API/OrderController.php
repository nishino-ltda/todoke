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
            'items' => 'required|array|min:1',
            'items.*.productId' => [
                'required',
                Rule::exists('products', 'id')->where(function ($query) use ($request) {
                    $query->where('restaurantId', $request->restaurantId);
                })
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'delivery.destination.lat' => 'required|numeric|between:-90,90',
            'delivery.destination.lng' => 'required|numeric|between:-180,180',
            'delivery.destination.address' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $order = Order::create([
            'clientId' => $request->user()->id,
            'restaurantId' => $request->restaurantId,
            'status' => 'pending',
            'totalValue' => 0
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $product = Product::find($item['productId']);
            $itemTotal = $product->price * $item['quantity'];
            $total += $itemTotal;
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['productId'],
                'quantity' => $item['quantity'],
                'unitPrice' => $product->price
            ]);
        }

        $order->update(['totalValue' => $total]);

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'totalValue' => number_format($order->totalValue, 2, '.', '')
        ], 201);
    }
}
