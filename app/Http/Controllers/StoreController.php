<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Inertia;

class StoreController extends Controller
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

            return Inertia::render('Public/PartnerStore', [
                'partner' => [
                    'id' => $partner->id,
                    'name' => $partner->business_name ?: $partner->name,
                    'slug' => $slug,
                    'type' => $partner->business_type,
                    'description' => $partner->description ?? '',
                    'latitude' => $partner->latitude ?? -23.5505 + (fmod($partner->id, 10) * 0.01),
                    'longitude' => $partner->longitude ?? -46.6333 + (fmod($partner->id, 10) * 0.01),
                ],
                'products' => $products->map(function ($product) use ($partner) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'price' => (float) $product->price,
                        'category' => $product->category,
                        'image' => $product->imageUrl,
                        'status' => $product->status,
                        'partner' => $partner->business_name ?: $partner->name,
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
            return Inertia::render('Public/PartnerStore', [
                'partner' => null,
                'products' => [],
            ]);
        }
    }
}
