<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = \App\Models\Product::where('partner_id', $request->user()->id)
            ->with('addons')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Partner/Products/Index', [
            'productsData' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'category' => $product->category,
                    'image' => $product->imageUrl,
                    'available' => $product->status === 'available',
                    'addons' => $product->addons->map(function ($addon) {
                        return [
                            'id' => $addon->id,
                            'name' => $addon->name,
                            'price' => (float) $addon->price,
                        ];
                    }),
                ];
            })
        ]);
    }

    public function create()
    {
        return Inertia::render('Partner/Products/Create');
    }

    public function edit($id)
    {
        return Inertia::render('Partner/Products/Edit', [
            'productId' => $id,
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Partner/Products/Show', [
            'productId' => $id,
        ]);
    }
}
