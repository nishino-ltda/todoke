<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function show($slug)
    {
        $partner = User::where('slug', $slug)
            ->where('type', 'partner')
            ->with(['products'])
            ->firstOrFail();

        return response()->json([
            'name' => $partner->business_name ?: $partner->name,
            'products' => $partner->products,
        ]);
    }
}
