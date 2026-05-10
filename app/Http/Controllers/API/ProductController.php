<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductController extends Controller
{
    public function partnerMenu(Request $request)
    {
        $products = Product::where('partner_id', $request->user()->id)
            ->with('addons')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($products->map(function ($product) {
            return $this->formatProduct($product);
        }));
    }

    public function partnerProducts(Request $request)
    {
        return $this->partnerMenu($request);
    }

    public function toggleAvailability(Request $request, $id)
    {
        $product = Product::where('partner_id', $request->user()->id)->findOrFail($id);
        $request->validate(['available' => 'required|boolean']);

        $product->update(['status' => $request->available ? 'available' : 'unavailable']);

        return response()->json($this->formatProduct($product->load('addons')));
    }

    public function partnerStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category' => 'required|string|max:255',
            'image' => 'nullable|string|max:2048',
            'available' => 'sometimes|boolean',
        ]);

        $product = Product::create([
            'partner_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category' => $request->category,
            'imageUrl' => $request->image,
            'status' => $request->has('available') ? ($request->available ? 'available' : 'unavailable') : 'available',
        ]);

        if ($request->has('addon_ids')) {
            $product->addons()->sync($request->addon_ids);
        }

        $product->load('addons');

        return response()->json($this->formatProduct($product), 201);
    }

    public function partnerUpdate(Request $request, Product $product)
    {
        if ($product->partner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string|max:255',
            'image' => 'nullable|string|max:2048',
            'available' => 'sometimes|boolean',
        ]);

        $data = $request->only(['name', 'description', 'price', 'category']);
        if ($request->has('image')) {
            $data['imageUrl'] = $request->image;
        }
        if ($request->has('available')) {
            $data['status'] = $request->available ? 'available' : 'unavailable';
        }

        $product->update($data);

        if ($request->has('addon_ids')) {
            $product->addons()->sync($request->addon_ids);
        }

        $product->load('addons');

        return response()->json($this->formatProduct($product));
    }

    public function partnerDestroy(Request $request, $id)
    {
        $product = Product::where('partner_id', $request->user()->id)->findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    private function formatProduct($product)
    {
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
    }

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
