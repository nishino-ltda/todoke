<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function show($slug)
    {
        $node = Node::where('identifier', $slug)
            ->where('type', 'partner')
            ->where('status', 'active')
            ->with(['partner', 'products' => function ($query) {
                $query->where('status', 'available');
            }])
            ->first();

        if (!$node) {
            return response()->json([
                'message' => __('Partner not found.')
            ], 404);
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
