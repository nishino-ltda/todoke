<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function setupOrders(Request $request)
    {
        $partner = User::where('email', 'partner@example.com')->firstOrFail();
        
        // Clear existing test orders
        Order::where('partner_id', $partner->id)->delete();

        // Create new test orders
        foreach ($request->orders as $orderData) {
            $order = Order::create([
                'partner_id' => $partner->id,
                'status' => $orderData['status'],
                'total_price' => 0 // Will be calculated from items
            ]);

            foreach ($orderData['items'] as $productName) {
                $product = Product::where('name', $productName)->firstOrFail();
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $product->price
                ]);
            }

            // Update order total
            $order->update([
                'total_price' => $order->items()->sum('price')
            ]);
        }

        return response()->json([
            'message' => 'Test orders created successfully',
            'count' => count($request->orders)
        ]);
    }
}
