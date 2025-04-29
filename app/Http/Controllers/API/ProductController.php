<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()->with('partner');

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $products = $query->where('status', 'available')->get();

        return response()->json([
            'products' => $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'category' => $product->category,
                    'status' => $product->status,
                    'partner' => $product->partner->name
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255'
        ]);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'partner_id' => $request->user()->id,
            'status' => 'available'
        ]);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:available,unavailable'
        ]);

        $product->update($request->all());

        return response()->json($product, 200);
    }
    
    public function addAddons(Request $request, Product $product)
    {
        $this->authorize('update', $product);
        
        $request->validate([
            'addon_ids' => 'required|array',
            'addon_ids.*' => 'exists:addons,id'
        ]);
        
        // Verify addons belong to the same partner as the product
        $addonCount = Addon::whereIn('id', $request->addon_ids)
            ->where('partner_id', $product->partner_id)
            ->count();
            
        if ($addonCount !== count($request->addon_ids)) {
            return response()->json([
                'message' => 'Some addons do not belong to this partner'
            ], 403);
        }
        
        $product->addons()->sync($request->addon_ids);
        
        return response()->json(['message' => 'Addons updated successfully']);
    }

    public function getAddons(Product $product)
    {
        $addons = $product->addons()->where('status', 'available')->get();
        
        return response()->json([
            'addons' => $addons->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'price' => $addon->price,
                    'status' => $addon->status
                ];
            })
        ]);
    }
}
