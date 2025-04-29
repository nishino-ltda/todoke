<?php

namespace App\Services;

use App\Models\Region;
use App\Models\VotingRound;
use App\Models\VotingOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VotingRoundService
{
    protected $calculationService;
    protected $fareUpdateService;

    public function __construct(
        VotingCalculationService $calculationService,
        FareUpdateService $fareUpdateService
    ) {
        $this->calculationService = $calculationService;
        $this->fareUpdateService = $fareUpdateService;
    }

    /**
     * Create a new voting round for regions.
     *
     * @param array $regionIds
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return VotingRound
     */
    public function createVotingRound(array $regionIds, Carbon $startDate, Carbon $endDate)
    {
        // Validate all regions exist
        $regions = Region::whereIn('id', $regionIds)->get();
        if ($regions->count() !== count($regionIds)) {
            throw new \Exception("One or more regions not found");
        }
        
        // Check for existing active voting rounds for these regions
        $existingRounds = VotingRound::whereIn('region_id', $regionIds)
            ->where('status', 'active')
            ->exists();
            
        if ($existingRounds) {
            throw new \Exception("Active voting rounds already exist for one or more regions");
        }
        
        // Create voting round with options in a transaction
        return DB::transaction(function () use ($regionIds, $startDate, $endDate) {
            // Create voting round
            $votingRound = VotingRound::create([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
            ]);
            
            // Attach regions
            $votingRound->regions()->attach($regionIds);
            
            // Generate and create voting options for each region
            foreach ($regionIds as $regionId) {
                $region = Region::find($regionId);
                $options = $this->generatePriceBandOptions($region);
                
                foreach ($options as $option) {
                    VotingOption::create([
                        'voting_round_id' => $votingRound->id,
                        'region_id' => $regionId,
                        'min_fare_per_km' => $option['min_fare_per_km'],
                        'avg_fare_per_km' => $option['avg_fare_per_km'],
                        'max_fare_per_km' => $option['max_fare_per_km'],
                    ]);
                }
            }
            
            return $votingRound->load(['votingOptions', 'regions']);
        });
    }
    
    /**
     * Schedule monthly voting rounds for all regions.
     * This could be called from a scheduled command.
     *
     * @return array
     */
    public function scheduleMonthlyVotingRounds()
    {
        $regions = Region::all();
        $results = [];
        
        foreach ($regions as $region) {
            try {
                // Set start time to now and end time to 30 days from now
                $startTime = Carbon::now();
                $endTime = Carbon::now()->addDays(30);
                
                // Generate price band options based on region's current pricing
                // and historical data (simplified for now)
                $options = $this->generatePriceBandOptions($region);
                
                // Create voting round
                $votingRound = $this->createVotingRound(
                    $region->id,
                    $startTime,
                    $endTime,
                    $options
                );
                
                $results[$region->id] = [
                    'success' => true,
                    'voting_round_id' => $votingRound->id,
                ];
            } catch (\Exception $e) {
                $results[$region->id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Close a voting round and calculate results.
     *
     * @param int $votingRoundId
     * @param VotingCalculationService $calculationService
     * @param FareUpdateService $fareUpdateService
     * @return array
     */
    public function closeVotingRound(
        int $votingRoundId, 
        VotingCalculationService $calculationService,
        FareUpdateService $fareUpdateService
    ) {
        $votingRound = VotingRound::findOrFail($votingRoundId);
        
        if ($votingRound->status !== 'active') {
            throw new \Exception("This voting round is not active.");
        }
        
        // Calculate results
        $results = $calculationService->calculateResults($votingRoundId);
        
        // Handle tie-breaking if needed
        $finalResults = $calculationService->handleTieBreak($results);
        
        // Get winning option
        $winningOption = $calculationService->getWinningOption($finalResults);
        
        // Update region's pricing based on winning option
        if ($winningOption) {
            $fareUpdateService->updateRegionPricing(
                $votingRound->region_id,
                $winningOption['min_fare_per_km'],
                $winningOption['avg_fare_per_km'],
                $winningOption['max_fare_per_km']
            );
        }
        
        // Update voting round status
        $votingRound->update([
            'status' => 'closed',
        ]);
        
        return [
            'voting_round' => $votingRound,
            'results' => $finalResults,
            'winning_option' => $winningOption,
        ];
    }
    
    /**
     * Check for expired voting rounds and close them.
     * This could be called from a scheduled command.
     *
     * @param VotingCalculationService $calculationService
     * @param FareUpdateService $fareUpdateService
     * @return array
     */
    public function processExpiredVotingRounds(
        VotingCalculationService $calculationService,
        FareUpdateService $fareUpdateService
    ) {
        $now = Carbon::now();
        
        // Find expired voting rounds
        $expiredRounds = VotingRound::where('status', 'active')
            ->where('end_time', '<', $now)
            ->get();
            
        $results = [];
        
        foreach ($expiredRounds as $round) {
            try {
                $result = $this->closeVotingRound(
                    $round->id,
                    $calculationService,
                    $fareUpdateService
                );
                
                $results[$round->id] = [
                    'success' => true,
                    'winning_option' => $result['winning_option'],
                ];
            } catch (\Exception $e) {
                $results[$round->id] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Generate price band options for a region.
     * This is a simplified implementation that could be expanded with more sophisticated logic.
     *
     * @param Region $region
     * @return array
     */
    protected function generatePriceBandOptions(Region $region)
    {
        // Get current pricing from region
        $currentMinFare = $region->community_min_fare_per_km ?? 1.0;
        $currentAvgFare = $region->community_avg_fare_per_km ?? 1.5;
        $currentMaxFare = $region->community_max_fare_per_km ?? 2.0;
        
        // Generate options with variations around current pricing
        return [
            // Option 1: Current pricing
            [
                'min_fare_per_km' => $currentMinFare,
                'avg_fare_per_km' => $currentAvgFare,
                'max_fare_per_km' => $currentMaxFare,
            ],
            // Option 2: Slight increase
            [
                'min_fare_per_km' => $currentMinFare * 1.05,
                'avg_fare_per_km' => $currentAvgFare * 1.05,
                'max_fare_per_km' => $currentMaxFare * 1.05,
            ],
            // Option 3: Moderate increase
            [
                'min_fare_per_km' => $currentMinFare * 1.10,
                'avg_fare_per_km' => $currentAvgFare * 1.10,
                'max_fare_per_km' => $currentMaxFare * 1.10,
            ],
            // Option 4: Slight decrease
            [
                'min_fare_per_km' => max($currentMinFare * 0.95, 0.5), // Ensure minimum doesn't go too low
                'avg_fare_per_km' => max($currentAvgFare * 0.95, 0.75),
                'max_fare_per_km' => max($currentMaxFare * 0.95, 1.0),
            ],
            // Option 5: Custom option based on recent trends (simplified)
            [
                'min_fare_per_km' => $currentMinFare * 1.03,
                'avg_fare_per_km' => $currentAvgFare * 1.07,
                'max_fare_per_km' => $currentMaxFare * 1.12,
            ],
        ];
    }
}
