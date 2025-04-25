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
            'restaurant_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(function ($query) use ($request) {
                    $query->where('restaurant_id', $request->restaurant_id);
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
            'customer_id' => $request->user()->id,
            'restaurant_id' => $request->restaurant_id,
            'status' => 'pending',
            'total_value' => 0
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];
            $total += $itemTotal;
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product->price
            ]);
        }

        $order->update(['total_value' => $total]);

        return response()->json([
            'id' => $order->id,
            'status' => $order->status,
            'total_value' => number_format($order->total_value, 2, '.', '')
        ], 201);
    }
}
