<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Region;
use App\Models\VotingRound;
use App\Models\VotingOption;
use App\Models\Vote;
use App\Services\VotingCalculationService;
use App\Services\VotingRoundService;
use App\Services\FareUpdateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class CommunityPricingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $region;
    protected $votingRound;
    protected $votingOptions;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();

        // Create test user
        $this->user = User::factory()->create([
            'type' => 'courier', // Ensure user is a courier
        ]);

        // Create test region
        $this->region = Region::factory()->create([
            'name' => 'Test Region',
            'community_min_fare_per_km' => 1.0,
            'community_avg_fare_per_km' => 1.5,
            'community_max_fare_per_km' => 2.0,
        ]);

        // Create test voting round
        $this->votingRound = VotingRound::factory()->create([
            'region_id' => $this->region->id,
            'start_time' => Carbon::now()->subDays(1),
            'end_time' => Carbon::now()->addDays(5),
            'status' => 'active',
        ]);

        // Create test voting options
        $this->votingOptions = [
            VotingOption::factory()->create([
                'voting_round_id' => $this->votingRound->id,
                'min_fare_per_km' => 1.0,
                'avg_fare_per_km' => 1.5,
                'max_fare_per_km' => 2.0,
            ]),
            VotingOption::factory()->create([
                'voting_round_id' => $this->votingRound->id,
                'min_fare_per_km' => 1.1,
                'avg_fare_per_km' => 1.6,
                'max_fare_per_km' => 2.1,
            ]),
            VotingOption::factory()->create([
                'voting_round_id' => $this->votingRound->id,
                'min_fare_per_km' => 1.2,
                'avg_fare_per_km' => 1.7,
                'max_fare_per_km' => 2.2,
            ]),
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /** @test */
    public function courier_can_submit_vote()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/voting/vote', [
                'voting_round_id' => $this->votingRound->id,
                'ranked_options' => [
                    $this->votingOptions[0]->id,
                    $this->votingOptions[1]->id,
                    $this->votingOptions[2]->id,
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Vote submitted successfully')
            ->assertJsonStructure(['vote']);

        $this->assertDatabaseHas('votes', [
            'voting_round_id' => $this->votingRound->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function courier_can_get_active_voting_rounds()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/voting/active');

        $response->assertStatus(200)
            ->assertJsonStructure(['voting_rounds']);
    }

    /** @test */
    public function courier_can_get_active_voting_round_for_region()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/voting/active?region_id=' . $this->region->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['voting_round']);
    }

    /** @test */
    public function borda_count_calculation_works_correctly()
    {
        // Create multiple users and votes
        $users = User::factory()->count(5)->create(['type' => 'courier']);
        
        // User 1 votes: Option 1 > Option 2 > Option 3
        Vote::create([
            'voting_round_id' => $this->votingRound->id,
            'user_id' => $users[0]->id,
            'ranked_options' => [
                $this->votingOptions[0]->id,
                $this->votingOptions[1]->id,
                $this->votingOptions[2]->id,
            ],
        ]);
        
        // User 2 votes: Option 2 > Option 1 > Option 3
        Vote::create([
            'voting_round_id' => $this->votingRound->id,
            'user_id' => $users[1]->id,
            'ranked_options' => [
                $this->votingOptions[1]->id,
                $this->votingOptions[0]->id,
                $this->votingOptions[2]->id,
            ],
        ]);
        
        // User 3 votes: Option 2 > Option 3 > Option 1
        Vote::create([
            'voting_round_id' => $this->votingRound->id,
            'user_id' => $users[2]->id,
            'ranked_options' => [
                $this->votingOptions[1]->id,
                $this->votingOptions[2]->id,
                $this->votingOptions[0]->id,
            ],
        ]);
        
        // User 4 votes: Option 3 > Option 1 > Option 2
        Vote::create([
            'voting_round_id' => $this->votingRound->id,
            'user_id' => $users[3]->id,
            'ranked_options' => [
                $this->votingOptions[2]->id,
                $this->votingOptions[0]->id,
                $this->votingOptions[1]->id,
            ],
        ]);
        
        // User 5 votes: Option 1 > Option 3 > Option 2
        Vote::create([
            'voting_round_id' => $this->votingRound->id,
            'user_id' => $users[4]->id,
            'ranked_options' => [
                $this->votingOptions[0]->id,
                $this->votingOptions[2]->id,
                $this->votingOptions[1]->id,
            ],
        ]);
        
        // Calculate results using the service
        $calculationService = new VotingCalculationService();
        $results = $calculationService->calculateResults($this->votingRound->id);
        
        // Expected points based on Borda count:
        // Option 1: (3 + 2 + 1 + 2 + 3) = 11 points
        // Option 2: (2 + 3 + 3 + 1 + 1) = 10 points
        // Option 3: (1 + 1 + 2 + 3 + 2) = 9 points
        
        // Get option IDs for easier comparison
        $option1Id = $this->votingOptions[0]->id;
        $option2Id = $this->votingOptions[1]->id;
        $option3Id = $this->votingOptions[2]->id;
        
        // Assert points
        $this->assertEquals(11, $results[$option1Id]['points']);
        $this->assertEquals(10, $results[$option2Id]['points']);
        $this->assertEquals(9, $results[$option3Id]['points']);
        
        // Assert winner
        $finalResults = $calculationService->handleTieBreak($results);
        $winner = $calculationService->getWinningOption($finalResults);
        
        $this->assertEquals($option1Id, $winner['option_id']);
    }

    #[Test]
    public function voting_round_service_can_close_round_and_update_pricing()
    {
        // Create votes (simplified from previous test)
        $user = User::factory()->create(['type' => 'courier']);
        Vote::create([
            'voting_round_id' => $this->votingRound->id,
            'user_id' => $user->id,
            'ranked_options' => [
                $this->votingOptions[1]->id, // Vote for option 2 (higher prices)
            ],
        ]);
        
        // Close the voting round
        $calculationService = new VotingCalculationService();
        $fareUpdateService = new FareUpdateService();
        $votingRoundService = new VotingRoundService(
            $calculationService,
            $fareUpdateService
        );
        
        $result = $votingRoundService->closeVotingRound(
            $this->votingRound->id,
            $calculationService,
            $fareUpdateService
        );
        
        // Assert voting round is closed
        $this->assertEquals('closed', VotingRound::find($this->votingRound->id)->status);
        
        // Assert region pricing is updated to match winning option
        $updatedRegion = Region::find($this->region->id);
        $winningOption = $this->votingOptions[1]; // Option 2 should win with only one vote
        
        $this->assertEquals($winningOption->min_fare_per_km, $updatedRegion->community_min_fare_per_km);
        $this->assertEquals($winningOption->avg_fare_per_km, $updatedRegion->community_avg_fare_per_km);
        $this->assertEquals($winningOption->max_fare_per_km, $updatedRegion->community_max_fare_per_km);
    }
}
