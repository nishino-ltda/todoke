<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Node;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MenuController extends Controller
{
    public function show($identifier)
    {
        try {
            $node = Node::where('identifier', $identifier)
                ->where('type', 'partner')
                ->where('status', 'active')
                ->with(['partner', 'products' => function ($query) {
                    $query->where('status', 'available');
                }])
                ->firstOrFail();

            $products = $node->products->load('addons');

            return Inertia::render('Customer/Menu', [
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
            ]);
        } catch (ModelNotFoundException $e) {
            return Inertia::render('Customer/Menu', [
                'partner' => null,
                'products' => [],
            ]);
        }
    }
}
