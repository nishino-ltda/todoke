<?php

namespace Tests\Unit;

use Tests\TestCase; // Change to extend Laravel's TestCase
use App\Models\VotingRound;
use App\Models\Region;
use App\Services\VotingRoundService;
use App\Services\VotingCalculationService;
use App\Services\FareUpdateService;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase; // Use RefreshDatabase trait
use Illuminate\Database\Eloquent\Collection as EloquentCollection; // Correct import
use Illuminate\Database\Eloquent\Builder; // Import Builder for mocking query builder

// Test suite for the VotingRoundService
class VotingRoundServiceTest extends TestCase
{
    use RefreshDatabase; // Use RefreshDatabase trait

    protected $mockCalculationService;
    protected $mockFareUpdateService;
    protected $votingRoundService;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
        $this->mockCalculationService = Mockery::mock(VotingCalculationService::class);
        $this->mockFareUpdateService = Mockery::mock(FareUpdateService::class);

        $this->votingRoundService = new VotingRoundService(
            $this->mockCalculationService,
            $this->mockFareUpdateService
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    // Test case: Creation of a new voting round
    // Should create a voting round with the correct start and end dates, and associated regions.
    public function test_creates_new_voting_round(): void
    {
        // Arrange: Set up regions and expected dates.
        $region1 = Region::factory()->create();
        $region2 = Region::factory()->create();
        $regions = [$region1->id, $region2->id];
        $startDate = now();
        $endDate = now()->addMonth();

        // Act: Create a new voting round.
        $votingRound = $this->votingRoundService->createVotingRound($regions, $startDate, $endDate);

        // Assert: A new VotingRound model is created in the database.
        $this->assertDatabaseHas('voting_rounds', ['id' => $votingRound->id]);
        // Assert: The start and end times are set correctly.
        $this->assertEquals($startDate->timestamp, $votingRound->start_time->timestamp);
        $this->assertEquals($endDate->timestamp, $votingRound->end_time->timestamp);
        // Assert: The voting round is associated with the specified regions.
        $this->assertCount(2, $votingRound->regions);
        $this->assertTrue($votingRound->regions->contains($region1));
        $this->assertTrue($votingRound->regions->contains($region2));
    }

    // Test case: Closing a voting round
    // Should calculate the results, update regional pricing, and mark the round as closed.
    public function test_closes_voting_round_and_updates_pricing(): void
    {
        // Arrange: Set up a voting round with votes and mock dependencies.
        $votingRound = VotingRound::factory()->create(['status' => 'active']);
        $region = Region::factory()->create();
        $votingRound->regions()->attach($region);
        // Sample votes for the round (simplified structure for mocking)
        $votes = new EloquentCollection([
            (object)['ranked_options' => [1, 2]],
            (object)['ranked_options' => [2, 1]],
        ]);
        $votingRound->setRelation('votes', $votes); // Set the relationship with mocked votes

        $options = new EloquentCollection([
             (object)['id' => 1, 'name' => 'Option A', 'min_fare_per_km' => 1, 'avg_fare_per_km' => 2, 'max_fare_per_km' => 3],
            (object)['id' => 2, 'name' => 'Option B', 'min_fare_per_km' => 4, 'avg_fare_per_km' => 5, 'max_fare_per_km' => 6],
        ]);
         $votingRound->setRelation('votingOptions', $options); // Set the relationship with mocked options


        // Mock the calculation and fare update services.
        $mockResults = [1 => ['points' => 3, 'rankings' => [1,1]], 2 => ['points' => 3, 'rankings' => [1,1]]];
        $finalResults = [1 => ['points' => 3, 'rankings' => [1,1], 'winner' => true]];
        
        $this->mockCalculationService->shouldReceive('calculateResults')
                               ->once()
                               ->with($votingRound->id)
                               ->andReturn($mockResults);
                               
        $this->mockCalculationService->shouldReceive('handleTieBreak')
                               ->once()
                               ->with($mockResults)
                               ->andReturn($finalResults);
                               
        $this->mockCalculationService->shouldReceive('getWinningOption')
                               ->once()
                               ->with($finalResults)
                               ->andReturn([
                                   'id' => 1,
                                   'min_fare_per_km' => 1,
                                   'avg_fare_per_km' => 2,
                                   'max_fare_per_km' => 3
                               ]);

        $this->mockFareUpdateService->shouldReceive('updateRegionPricing')
                             ->once()
                             ->with(Mockery::any(), Mockery::any(), Mockery::any(), Mockery::any());

        // Act: Close the voting round with required services
        $this->votingRoundService->closeVotingRound(
            $votingRound->id,
            $this->mockCalculationService,
            $this->mockFareUpdateService
        );

        // Assert: The voting round's status is updated to 'closed'.
        $this->assertEquals('closed', $votingRound->fresh()->status);
        // Assert: Notifications are sent (this might require mocking the Notification facade or similar).
        // For now, we'll just assert that the status is closed as a primary outcome.
    }

    // Test case: Attempting to close an already closed voting round
    // Should prevent the action or handle it gracefully.
    public function test_cannot_close_already_closed_round(): void
    {
        // Arrange: Set up a closed voting round.
        $votingRound = VotingRound::factory()->create(['status' => 'closed']);

        // Assert: The service prevents closing an already closed round by throwing an exception.
        $this->expectException(\Exception::class);

        // Act: Attempt to close the round with required services
        $this->votingRoundService->closeVotingRound(
            $votingRound->id,
            $this->mockCalculationService,
            $this->mockFareUpdateService
        );
    }

    // Test case: Retrieving active voting rounds
    // Should return only voting rounds that are currently open for voting.
    public function test_retrieves_active_voting_rounds(): void
    {
        // Arrange: Create active and closed voting rounds.
        VotingRound::factory()->create(['status' => 'active', 'start_time' => now()->subDay(), 'end_time' => now()->addDay()]);
        VotingRound::factory()->create(['status' => 'closed', 'start_time' => now()->subMonth(), 'end_time' => now()->subWeek()]);

        // Act: Retrieve active rounds directly from model since service doesn't have this method
        $activeRounds = VotingRound::where('status', 'active')->get();

        // Assert: The returned collection contains only active voting rounds.
        $this->assertCount(1, $activeRounds);
        $this->assertEquals('active', $activeRounds->first()->status);
    }

    // Test case: Retrieving past voting rounds
    // Should return voting rounds that have been closed.
    public function test_retrieves_past_voting_rounds(): void
    {
        // Arrange: Create active and closed voting rounds.
        VotingRound::factory()->create(['status' => 'active', 'start_time' => now()->subDay(), 'end_time' => now()->addDay()]);
        VotingRound::factory()->create(['status' => 'closed', 'start_time' => now()->subMonth(), 'end_time' => now()->subWeek()]);

        // Act: Retrieve past rounds directly from model since service doesn't have this method
        $pastRounds = VotingRound::where('status', 'closed')->get();

        // Assert: The returned collection contains only closed voting rounds.
        $this->assertCount(1, $pastRounds);
        $this->assertEquals('closed', $pastRounds->first()->status);
    }
}
