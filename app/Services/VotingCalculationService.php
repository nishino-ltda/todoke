<?php

namespace App\Services;

use App\Models\VotingRound;
use App\Models\VotingOption;
use Illuminate\Support\Collection;

class VotingCalculationService
{
    /**
     * Calculate the results of a voting round using the Borda count method.
     *
     * @param int $votingRoundId
     * @return array
     */
    public function calculateResults(int $votingRoundId): array
    {
        $votingRound = VotingRound::with(['votingOptions', 'votes'])->findOrFail($votingRoundId);
        $options = $votingRound->votingOptions;
        $votes = $votingRound->votes;
        
        return $this->calculateBordaCount($votes, $options);
    }
    
    /**
     * Calculate Borda count for a set of votes and options.
     * 
     * In Borda count, each option gets points based on its ranking:
     * - 1st place: n points (where n is the number of options)
     * - 2nd place: n-1 points
     * - 3rd place: n-2 points
     * And so on...
     *
     * @param Collection $votes
     * @param Collection $options
     * @return array
     */
    protected function calculateBordaCount(Collection $votes, Collection $options): array
    {
        $results = [];
        $optionCount = $options->count();
        
        // Initialize results array with zero points for each option
        foreach ($options as $option) {
            $results[$option->id] = [
                'option_id' => $option->id,
                'min_fare_per_km' => $option->min_fare_per_km,
                'avg_fare_per_km' => $option->avg_fare_per_km,
                'max_fare_per_km' => $option->max_fare_per_km,
                'points' => 0,
                'rankings' => array_fill(0, $optionCount, 0) // Count of each ranking position
            ];
        }
        
        // Calculate points for each option based on rankings
        foreach ($votes as $vote) {
            $rankedOptions = $vote->ranked_options;
            $points = $optionCount; // Max points for first choice
            
            foreach ($rankedOptions as $index => $optionId) {
                if (isset($results[$optionId])) {
                    $results[$optionId]['points'] += $points;
                    $results[$optionId]['rankings'][$index]++; // Count this ranking position
                    $points--; // Decrease points for next ranking
                }
            }
        }
        
        // Sort results by points (descending)
        uasort($results, function ($a, $b) {
            return $b['points'] <=> $a['points'];
        });
        
        return $results;
    }
    
    /**
     * Get the winning option from calculated results.
     *
     * @param array $results
     * @return array|null
     */
    public function getWinningOption(array $results): ?array
    {
        if (empty($results)) {
            return null;
        }
        
        // First element is the winner (results are already sorted)
        return reset($results);
    }
    
    /**
     * Handle tie-breaking if needed.
     * This is a simple implementation that could be expanded with more sophisticated tie-breaking rules.
     *
     * @param array $results
     * @return array
     */
    public function handleTieBreak(array $results): array
    {
        // Group options by points
        $pointGroups = [];
        foreach ($results as $optionId => $result) {
            $points = $result['points'];
            if (!isset($pointGroups[$points])) {
                $pointGroups[$points] = [];
            }
            $pointGroups[$points][$optionId] = $result;
        }
        
        // Sort point groups by key (descending)
        krsort($pointGroups);
        
        $finalResults = [];
        foreach ($pointGroups as $points => $options) {
            if (count($options) > 1) {
                // Tie-breaking: prefer option with more first-place votes
                uasort($options, function ($a, $b) {
                    // Compare first-place votes
                    if ($a['rankings'][0] !== $b['rankings'][0]) {
                        return $b['rankings'][0] <=> $a['rankings'][0];
                    }
                    
                    // If still tied, compare second-place votes, and so on
                    for ($i = 1; $i < count($a['rankings']); $i++) {
                        if ($a['rankings'][$i] !== $b['rankings'][$i]) {
                            return $b['rankings'][$i] <=> $a['rankings'][$i];
                        }
                    }
                    
                    return 0; // Complete tie
                });
            }
            
            // Add tie-broken options to final results
            foreach ($options as $optionId => $option) {
                $finalResults[$optionId] = $option;
            }
        }
        
        return $finalResults;
    }
}
