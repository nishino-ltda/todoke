<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function dashboard(Request $request)
    {
        $partnerId = $request->user()->id;

        $newOrders = \App\Models\Order::where('partner_id', $partnerId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $preparing = \App\Models\Order::where('partner_id', $partnerId)
            ->where('status', 'preparing')
            ->count();

        $completed = \App\Models\Order::where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->count();

        $todayRevenue = \App\Models\Order::where('partner_id', $partnerId)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->sum('total_value');

        return response()->json([
            'new_orders' => $newOrders,
            'preparing' => $preparing,
            'today_revenue' => (float) $todayRevenue,
            'completed' => $completed,
        ]);
    }

    public function show($slug)
    {
        $partner = User::where('slug', $slug)
            ->where('type', 'partner')
            ->where('status', 'active')
            ->with(['products' => function ($query) {
                $query->where('status', 'available');
            }])
            ->first();

        if (!$partner) {
            return response()->json([
                'message' => __('Partner not found.')
            ], 404);
        }

        $products = $partner->products->load('addons');

        return response()->json([
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->business_name ?: $partner->name,
                'slug' => $slug,
                'type' => $partner->business_type,
                'latitude' => $partner->latitude,
                'longitude' => $partner->longitude,
                'address' => $partner->address,
            ],
            'products' => $products->map(function ($product) {
                return [
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'category' => $product->category,
                    'image' => $product->imageUrl,
                    'status' => $product->status,
                    'addons' => $product->addons->map(function ($addon) {
                        return [
                            'name' => $addon->name,
                            'description' => $addon->description,
                            'price' => (float) $addon->price,
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100',
        ]);

        $lat = (float) $request->lat;
        $lng = (float) $request->lng;
        $radius = (float) ($request->radius ?? 10);

        $partners = User::where('type', 'partner')
            ->where('status', 'active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(function ($partner) use ($lat, $lng, $radius) {
                $distance = $this->haversine($lat, $lng, (float) $partner->latitude, (float) $partner->longitude);
                $partner->distance_km = round($distance, 2);
                return $distance <= $radius;
            })
            ->values();

        return response()->json(['data' => $partners]);
    }

    protected function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function metrics(Request $request)
    {
        $partnerId = $request->user()->id;

        $totalDeliveries = \App\Models\Delivery::where('logistics_partner_id', $partnerId)->count();
        $averageDeliveryTime = \App\Models\Delivery::where('logistics_partner_id', $partnerId)
            ->avg('estimated_time');

        $averageRating = \App\Models\Rating::where('rated_id', $partnerId)
            ->avg('rating');

        return response()->json([
            'data' => [
                'totalDeliveries' => $totalDeliveries,
                'averageDeliveryTime' => (float)($averageDeliveryTime ?? 0),
                'averageRating' => (float)($averageRating ?? 0),
                'deliveriesPerDay' => [] // Stub for now
            ]
        ]);
    }
}
