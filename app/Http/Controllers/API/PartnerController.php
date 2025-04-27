<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    /**
     * Get partner metrics
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function metrics(Request $request)
    {
        $partner = $request->user();

        // Total deliveries assigned to this partner
        $totalDeliveries = Delivery::where('logistics_partner_id', $partner->id)
            ->count();

        // Average delivery time (in minutes)
        $avgDeliveryTime = Delivery::where('logistics_partner_id', $partner->id)
            ->where('status', 'delivered')
            ->select(DB::raw('AVG(estimated_time) as avg_time'))
            ->value('avg_time');

        // Average rating
        $avgRating = \App\Models\Rating::where('rated_id', $partner->id)
            ->select(DB::raw('AVG(rating) as avg_rating'))
            ->value('avg_rating');

        // Deliveries per day (last 7 days)
        $deliveriesPerDay = Delivery::where('logistics_partner_id', $partner->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        return response()->json([
            'data' => [
                'totalDeliveries' => $totalDeliveries,
                'averageDeliveryTime' => round($avgDeliveryTime, 2),
                'averageRating' => round($avgRating, 1),
                'deliveriesPerDay' => $deliveriesPerDay
            ]
        ]);
    }
}
