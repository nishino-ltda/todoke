<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VotingRound;
use App\Models\VotingOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vote>
 */
class VoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Create a voting round with options if not provided
        $votingRound = VotingRound::factory()->create();
        
        // Create options for this round if needed
        $options = VotingOption::where('voting_round_id', $votingRound->id)->get();
        if ($options->isEmpty()) {
            $options = VotingOption::factory()->count(3)->create([
                'voting_round_id' => $votingRound->id
            ]);
        }
        
        // Get option IDs
        $optionIds = $options->pluck('id')->toArray();
        
        // Shuffle option IDs to create a random ranking
        shuffle($optionIds);
        
        return [
            'voting_round_id' => $votingRound->id,
            'user_id' => User::factory()->create(['role' => 'courier']),
            'ranked_options' => $optionIds,
        ];
    }
    
    /**
     * Configure the model factory to use a specific voting round.
     *
     * @param VotingRound $votingRound
     * @return static
     */
    public function forVotingRound(VotingRound $votingRound): static
    {
        return $this->state(function (array $attributes) use ($votingRound) {
            // Get options for this round
            $options = VotingOption::where('voting_round_id', $votingRound->id)->get();
            if ($options->isEmpty()) {
                $options = VotingOption::factory()->count(3)->create([
                    'voting_round_id' => $votingRound->id
                ]);
            }
            
            // Get option IDs
            $optionIds = $options->pluck('id')->toArray();
            
            // Shuffle option IDs to create a random ranking
            shuffle($optionIds);
            
            return [
                'voting_round_id' => $votingRound->id,
                'ranked_options' => $optionIds,
            ];
        });
    }
    
    /**
     * Configure the model factory to use a specific user.
     *
     * @param User $user
     * @return static
     */
    public function byUser(User $user): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user->id,
            ];
        });
    }
    
    /**
     * Configure the model factory to use a specific ranking of options.
     *
     * @param array $rankedOptionIds
     * @return static
     */
    public function withRanking(array $rankedOptionIds): static
    {
        return $this->state(function (array $attributes) use ($rankedOptionIds) {
            return [
                'ranked_options' => $rankedOptionIds,
            ];
        });
    }
}
