<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => [
                'required',
                Rule::exists('products', 'id')->where(function ($query) use ($request) {
                    $query->where('partner_id', $request->partner_id);
                })
            ],
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*.addon_id' => 'exists:addons,id',
            'items.*.addons.*.quantity' => 'integer|min:1',
            'delivery.destination.lat' => 'required|numeric|between:-90,90',
            'delivery.destination.lng' => 'required|numeric|between:-180,180',
            'delivery.destination.address' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $order = Order::create([
            'customer_id' => $request->user()->id,
            'partner_id' => $request->partner_id,
            'status' => 'pending',
            'total_value' => 0
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];
            
            // Calculate addons price
            $selectedAddons = [];
            if (isset($item['addons']) && is_array($item['addons'])) {
                foreach ($item['addons'] as $addonItem) {
                    $addon = Addon::find($addonItem['addon_id']);
                    
                    // Verify addon is compatible with this product
                    if (!$product->addons()->where('addon_id', $addon->id)->exists()) {
                        return response()->json([
                            'message' => "Addon {$addon->name} is not compatible with {$product->name}"
                        ], 400);
                    }
                    
                    $addonPrice = $addon->price * $addonItem['quantity'];
                    $itemTotal += $addonPrice;
                    
                    $selectedAddons[] = [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'quantity' => $addonItem['quantity'],
                        'unit_price' => $addon->price
                    ];
                }
            }
            
            $total += $itemTotal;
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'selected_addons' => !empty($selectedAddons) ? $selectedAddons : null
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
