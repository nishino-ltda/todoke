<?php

namespace App\Services;

use App\Models\Vote;
use App\Models\VotingRound;
use App\Models\VotingOption;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class VotingService
{
    /**
     * Submit a vote for a voting round.
     *
     * @param int $votingRoundId
     * @param array $rankedOptions Array of option IDs in ranked order
     * @param int|null $userId
     * @return Vote
     * @throws ValidationException
     */
    public function submitVote(int $votingRoundId, array $rankedOptions, ?int $userId = null)
    {
        $userId = $userId ?? Auth::id();
        
        // Validate voting round exists and is active
        $votingRound = VotingRound::findOrFail($votingRoundId);
        
        if ($votingRound->status !== 'active') {
            throw ValidationException::withMessages([
                'voting_round_id' => ['This voting round is not active.'],
            ]);
        }
        
        // Validate all options belong to this voting round
        $optionIds = $votingRound->votingOptions()->pluck('id')->toArray();
        
        foreach ($rankedOptions as $optionId) {
            if (!in_array($optionId, $optionIds)) {
                throw ValidationException::withMessages([
                    'ranked_options' => ['Invalid option ID provided.'],
                ]);
            }
        }
        
        // Check if user has already voted in this round
        $existingVote = Vote::where('voting_round_id', $votingRoundId)
            ->where('user_id', $userId)
            ->first();
            
        if ($existingVote) {
            // Update existing vote
            $existingVote->update([
                'ranked_options' => $rankedOptions,
            ]);
            
            return $existingVote;
        }
        
        // Create new vote
        return Vote::create([
            'voting_round_id' => $votingRoundId,
            'user_id' => $userId,
            'ranked_options' => $rankedOptions,
        ]);
    }
    
    /**
     * Get active voting round for a region.
     *
     * @param int|null $regionId
     * @return VotingRound|null
     */
    public function getActiveVotingRound(?int $regionId = null)
    {
        $query = VotingRound::where('status', 'active');
        
        if ($regionId) {
            $query->where('region_id', $regionId);
        }
        
        return $query->with('votingOptions')->first();
    }
    
    /**
     * Get all active voting rounds.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActiveVotingRounds()
    {
        return VotingRound::where('status', 'active')
            ->with('votingOptions', 'region')
            ->get();
    }
}
