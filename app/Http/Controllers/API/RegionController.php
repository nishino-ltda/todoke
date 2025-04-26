<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'polygon' => 'required|array',
            'polygon.type' => 'required|string|in:Polygon',
            'polygon.coordinates' => 'required|array'
        ]);

        $region = Region::create([
            'name' => $validated['name'],
            'polygon' => $validated['polygon'],
            'partner_id' => $request->user()->id
        ]);

        return response()->json([
            'data' => $region
        ], 201);
    }
}
