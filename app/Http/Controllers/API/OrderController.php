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
    public function partnerIndex(Request $request)
    {
        $orders = Order::where('partner_id', $request->user()->id)
            ->with(['items.product', 'client', 'delivery'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders->map(function ($order) {
            return $this->formatOrder($order);
        }));
    }

    public function partnerShow(Request $request, $id)
    {
        $order = Order::where('partner_id', $request->user()->id)
            ->with(['items.product', 'client', 'delivery'])
            ->findOrFail($id);

        return response()->json($this->formatOrder($order));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:pending,preparing,ready,completed,cancelled',
        ]);

        $order = Order::where('partner_id', $request->user()->id)->findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json($this->formatOrder($order->load(['items.product', 'client', 'delivery'])));
    }

    private function formatOrder($order)
    {
        return [
            'id' => $order->id,
            'customer_name' => $order->client->name ?? 'N/A',
            'customer_phone' => $order->client->phone ?? '',
            'status' => $order->status,
            'total' => (float) $order->total_value,
            'subtotal' => (float) $order->total_value,
            'delivery_fee' => 0.00,
            'delivery_address' => $order->delivery?->destination['address'] ?? 'N/A',
            'payment_method' => $order->delivery?->payment_method ?? 'credit_card',
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product->name ?? 'Product',
                    'quantity' => $item->quantity,
                    'price' => (float) $item->unit_price,
                    'addons' => collect($item->selected_addons)->map(function ($addon) {
                        return [
                            'id' => $addon['id'] ?? 0,
                            'name' => $addon['name'] ?? '',
                            'price' => (float) ($addon['unit_price'] ?? 0),
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];
    }

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
