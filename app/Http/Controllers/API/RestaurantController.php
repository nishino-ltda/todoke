<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function show($slug)
    {
        $restaurant = Node::where('identifier', $slug)
            ->where('type', 'partner')
            ->with(['partner', 'region', 'products'])
            ->firstOrFail();

        return response()->json([
            'name' => $restaurant->identifier,
            'products' => $restaurant->products,
            'partner' => $restaurant->partner,
            'region' => $restaurant->region
        ]);
    }
}
