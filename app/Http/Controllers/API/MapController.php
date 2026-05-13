<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function __construct(
        protected GeocodingService $geocodingService
    ) {}

    public function geocode(Request $request)
    {
        $request->validate([
            'address' => 'required|string'
        ]);

        $result = $this->geocodingService->geocode($request->address);

        return response()->json([
            'data' => $result
        ]);
    }

    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric'
        ]);

        $result = $this->geocodingService->reverseGeocode(
            (float) $request->lat,
            (float) $request->lng
        );

        return response()->json([
            'data' => $result
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

        $result = $this->geocodingService->getDistance(
            ['lat' => (float) $request->origin_lat, 'lng' => (float) $request->origin_lng],
            ['lat' => (float) $request->dest_lat, 'lng' => (float) $request->dest_lng]
        );

        return response()->json([
            'data' => $result
        ]);
    }
}
