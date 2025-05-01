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
            ->with(['partner', 'region'])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id' => $restaurant->id,
                'name' => $restaurant->identifier,
                'partner' => $restaurant->partner,
                'region' => $restaurant->region,
                'status' => $restaurant->status,
                'capacity' => $restaurant->capacity
            ]
        ]);
    }
}
