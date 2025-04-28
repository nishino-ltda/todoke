<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VotingRound;
use App\Services\VotingService;
use App\Services\VotingCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VotingController extends Controller
{
    protected $votingService;
    protected $calculationService;

    public function __construct(
        VotingService $votingService,
        VotingCalculationService $calculationService
    ) {
        $this->votingService = $votingService;
        $this->calculationService = $calculationService;
    }

    /**
     * Submit a vote for an active voting round.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function vote(Request $request)
    {
        try {
            $validated = $request->validate([
                'voting_round_id' => 'required|integer|exists:voting_rounds,id',
                'ranked_options' => 'required|array',
                'ranked_options.*' => 'integer|exists:voting_options,id',
            ]);

            $vote = $this->votingService->submitVote(
                $validated['voting_round_id'],
                $validated['ranked_options']
            );

            return response()->json([
                'message' => 'Vote submitted successfully',
                'vote' => $vote
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to submit vote',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active voting rounds.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveRounds(Request $request)
    {
        try {
            $regionId = $request->query('region_id');
            
            if ($regionId) {
                $votingRound = $this->votingService->getActiveVotingRound($regionId);
                
                if (!$votingRound) {
                    return response()->json([
                        'message' => 'No active voting round found for this region'
                    ], 404);
                }
                
                return response()->json([
                    'voting_round' => $votingRound
                ]);
            } else {
                $votingRounds = $this->votingService->getAllActiveVotingRounds();
                
                return response()->json([
                    'voting_rounds' => $votingRounds
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve active voting rounds',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get results for a specific voting round.
     *
     * @param int $roundId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResults($roundId)
    {
        try {
            $votingRound = VotingRound::findOrFail($roundId);
            
            // Only allow viewing results for closed rounds
            if ($votingRound->status !== 'closed') {
                return response()->json([
                    'message' => 'Results are only available for closed voting rounds'
                ], 403);
            }
            
            $results = $this->calculationService->calculateResults($roundId);
            $finalResults = $this->calculationService->handleTieBreak($results);
            $winningOption = $this->calculationService->getWinningOption($finalResults);
            
            return response()->json([
                'voting_round' => $votingRound,
                'results' => $finalResults,
                'winning_option' => $winningOption
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve voting results',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
