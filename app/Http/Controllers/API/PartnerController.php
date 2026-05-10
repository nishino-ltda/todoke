<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function dashboard(Request $request)
    {
        $partnerId = $request->user()->id;

        $newOrders = \App\Models\Order::where('partner_id', $partnerId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $preparing = \App\Models\Order::where('partner_id', $partnerId)
            ->where('status', 'preparing')
            ->count();

        $completed = \App\Models\Order::where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->count();

        $todayRevenue = \App\Models\Order::where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->sum('total_value');

        return response()->json([
            'new_orders' => $newOrders,
            'preparing' => $preparing,
            'today_revenue' => (float) $todayRevenue,
            'completed' => $completed,
        ]);
    }

    public function show($slug)
    {
        $partner = User::where('slug', $slug)
            ->where('type', 'partner')
            ->where('status', 'active')
            ->with(['products' => function ($query) {
                $query->where('status', 'available');
            }])
            ->first();

        if (!$partner) {
            return response()->json([
                'message' => __('Partner not found.')
            ], 404);
        }

        $products = $partner->products->load('addons');

        return response()->json([
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->business_name ?: $partner->name,
                'slug' => $slug,
                'type' => $partner->business_type,
            ],
            'products' => $products->map(function ($product) {
                return [
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'category' => $product->category,
                    'image' => $product->imageUrl,
                    'status' => $product->status,
                    'addons' => $product->addons->map(function ($addon) {
                        return [
                            'name' => $addon->name,
                            'description' => $addon->description,
                            'price' => (float) $addon->price,
                        ];
                    }),
                ];
            }),
            'metrics' => $partner->metrics()->count() ? $partner->metrics : null,
        ]);
    }

        $products = $node->products->load('addons');

        return response()->json([
            'partner' => [
                'id' => $node->partner->id,
                'name' => $node->partner->business_name ?: $node->partner->name,
                'slug' => $node->identifier,
                'type' => $node->partner->business_type,
            ],
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'category' => $product->category,
                    'image' => $product->imageUrl,
                    'status' => $product->status,
                    'addons' => $product->addons->map(function ($addon) {
                        return [
                            'id' => $addon->id,
                            'name' => $addon->name,
                            'description' => $addon->description,
                            'price' => (float) $addon->price,
                        ];
                    }),
                ];
            }),
            'metrics' => $node->partner->metrics()->count() ? $node->partner->metrics : null,
        ]);
    }
}
