<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddonController extends Controller
{
    public function index(Request $request)
    {
        $query = Addon::query()->with('partner');
        
        if ($request->has('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }
        
        $addons = $query->where('status', 'available')->get();
        
        return response()->json([
            'addons' => $addons->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'price' => $addon->price,
                    'status' => $addon->status,
                    'partner' => $addon->partner->name
                ];
            })
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0'
        ]);
        
        $addon = Addon::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'partner_id' => $request->user()->id,
            'status' => 'available'
        ]);
        
        return response()->json($addon, 201);
    }
    
    public function update(Request $request, Addon $addon)
    {
        // Authorization check
        if ($addon->partner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:available,unavailable'
        ]);
        
        $addon->update($request->all());
        
        return response()->json($addon, 200);
    }
    
    public function destroy(Request $request, Addon $addon)
    {
        // Authorization check
        if ($addon->partner_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $addon->delete();
        
        return response()->json(['message' => 'Addon deleted successfully']);
    }
}
