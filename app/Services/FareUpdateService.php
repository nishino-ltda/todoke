<?php

namespace App\Services;

use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FareUpdateService
{
    /**
     * Update a region's pricing based on voting results.
     *
     * @param int $regionId
     * @param float $minFarePerKm
     * @param float $avgFarePerKm
     * @param float $maxFarePerKm
     * @return Region
     */
    public function updateRegionPricing(
        int $regionId,
        float $minFarePerKm,
        float $avgFarePerKm,
        float $maxFarePerKm
    ) {
        $region = Region::findOrFail($regionId);
        
        // Update region with new community pricing
        $region->update([
            'community_min_fare_per_km' => $minFarePerKm,
            'community_avg_fare_per_km' => $avgFarePerKm,
            'community_max_fare_per_km' => $maxFarePerKm,
        ]);
        
        // Log the pricing update
        Log::info("Updated pricing for region {$regionId}", [
            'min_fare_per_km' => $minFarePerKm,
            'avg_fare_per_km' => $avgFarePerKm,
            'max_fare_per_km' => $maxFarePerKm,
        ]);
        
        return $region;
    }
    
    /**
     * Calculate fare for a delivery based on region's community pricing.
     *
     * @param int $regionId
     * @param float $distanceKm
     * @param string $timeOfDay
     * @param float $demandFactor
     * @return array
     */
    public function calculateDeliveryFare(
        int $regionId,
        float $distanceKm,
        string $timeOfDay = 'normal',
        float $demandFactor = 1.0
    ) {
        $region = Region::findOrFail($regionId);
        
        // Get base fare per km from region's community pricing
        $baseFarePerKm = $region->community_avg_fare_per_km ?? 1.5;
        
        // Apply time of day adjustment
        $timeMultiplier = $this->getTimeOfDayMultiplier($timeOfDay);
        
        // Calculate adjusted fare
        $adjustedFarePerKm = $baseFarePerKm * $timeMultiplier * $demandFactor;
        
        // Calculate total fare
        $totalFare = $adjustedFarePerKm * $distanceKm;
        
        // Apply minimum fare if needed
        $minFare = max(5.0, $region->community_min_fare_per_km * $distanceKm);
        $totalFare = max($totalFare, $minFare);
        
        // Apply maximum fare if needed
        $maxFare = $region->community_max_fare_per_km * $distanceKm;
        if ($maxFare > 0) {
            $totalFare = min($totalFare, $maxFare);
        }
        
        // Round to 2 decimal places
        $totalFare = round($totalFare, 2);
        
        return [
            'base_fare_per_km' => $baseFarePerKm,
            'adjusted_fare_per_km' => $adjustedFarePerKm,
            'distance_km' => $distanceKm,
            'time_multiplier' => $timeMultiplier,
            'demand_factor' => $demandFactor,
            'total_fare' => $totalFare,
        ];
    }
    
    /**
     * Get multiplier based on time of day.
     *
     * @param string $timeOfDay
     * @return float
     */
    protected function getTimeOfDayMultiplier(string $timeOfDay): float
    {
        return match ($timeOfDay) {
            'night' => 1.2,
            'peak' => 1.15,
            'weekend' => 1.1,
            'holiday' => 1.25,
            default => 1.0, // normal
        };
    }
    
    /**
     * Apply the current community pricing to all pending deliveries in a region.
     *
     * @param int $regionId
     * @return int Number of deliveries updated
     */
    public function updatePendingDeliveryPricing(int $regionId)
    {
        // This would update pricing for pending deliveries
        // Implementation would depend on how deliveries are stored and priced
        // Simplified example:
        
        /*
        return DB::table('deliveries')
            ->where('region_id', $regionId)
            ->where('status', 'pending')
            ->update([
                'pricing_updated_at' => now(),
                // Other pricing fields would be updated here
            ]);
        */
        
        // Placeholder return
        return 0;
    }
}
