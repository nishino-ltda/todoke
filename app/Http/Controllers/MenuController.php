<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MenuController extends Controller
{
    public function show($slug)
    {
        try {
            $partner = User::where('slug', $slug)
                ->where('type', 'partner')
                ->where('status', 'active')
                ->with(['products' => function ($query) {
                    $query->where('status', 'available');
                }])
                ->firstOrFail();

            $products = $partner->products->load('addons');

            return Inertia::render('Customer/Menu', [
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
            ]);
        } catch (ModelNotFoundException $e) {
            return Inertia::render('Customer/Menu', [
                'partner' => null,
                'products' => [],
            ]);
        }
    }
}
