<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function geocode(Request $request)
    {
        $request->validate([
            'address' => 'required|string'
        ]);

        // Mock geocoding response
        return response()->json([
            'data' => [
                'address' => $request->address,
                'lat' => -23.5505,
                'lng' => -46.6333
            ]
        ]);
    }

    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        // Mock reverse geocoding response
        return response()->json([
            'data' => [
                'address' => 'Praça da Sé, São Paulo - SP, Brazil',
                'lat' => $request->lat,
                'lng' => $request->lng
            ]
        ]);
    }

    public function distance(Request $request)
    {
        $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'dest_lat' => 'required|numeric',
            'dest_lng' => 'required|numeric'
        ]);

        // Mock distance calculation
        return response()->json([
            'data' => [
                'distance_km' => 2.5,
                'duration_seconds' => 600
            ]
        ]);
    }
}
