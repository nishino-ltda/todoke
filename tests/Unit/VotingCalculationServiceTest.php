<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;
use App\Services\VotingCalculationService;
use App\Models\VotingRound;
use App\Models\VotingOption;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Mockery;

// Test suite for the VotingCalculationService
class VotingCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    // Test case: calculateResults method with a simple scenario
    public function test_calculate_results_with_simple_scenario(): void
    {
        // Arrange: Mock VotingRound and its relationships
        $votingRoundId = 1;
        $options = new EloquentCollection([
            (object)['id' => 1, 'name' => 'Option A', 'min_fare_per_km' => 1, 'avg_fare_per_km' => 2, 'max_fare_per_km' => 3],
            (object)['id' => 2, 'name' => 'Option B', 'min_fare_per_km' => 4, 'avg_fare_per_km' => 5, 'max_fare_per_km' => 6],
            (object)['id' => 3, 'name' => 'Option C', 'min_fare_per_km' => 7, 'avg_fare_per_km' => 8, 'max_fare_per_km' => 9],
        ]);
        $votes = new EloquentCollection([
            (object)['ranked_options' => [1, 2, 3]],
            (object)['ranked_options' => [1, 3, 2]],
            (object)['ranked_options' => [1, 2, 3]],
        ]);

        $votingRoundMock = Mockery::mock('overload:' . VotingRound::class);
        $votingRoundMock->shouldReceive('with')->with(['votingOptions', 'votes'])->andReturn($votingRoundMock);
        $votingRoundMock->shouldReceive('findOrFail')->with($votingRoundId)->andReturn($votingRoundMock);
        $votingRoundMock->votingOptions = $options;
        $votingRoundMock->votes = $votes;

        $service = new VotingCalculationService();

        // Act: Perform the calculation
        $results = $service->calculateResults($votingRoundId);

        // Assert: The calculated results are correct
        $this->assertIsArray($results);
        $this->assertCount(3, $results);

        $this->assertEquals(9, $results[1]['points']);
        $this->assertEquals(5, $results[2]['points']);
        $this->assertEquals(4, $results[3]['points']);

        $this->assertEquals([3, 0, 0], $results[1]['rankings']);
        $this->assertEquals([0, 2, 1], $results[2]['rankings']);
        $this->assertEquals([0, 1, 2], $results[3]['rankings']);
    }

    // Test case: getWinningOption method with results
    public function test_get_winning_option_with_results(): void
    {
        // Arrange: Set up calculated results
        $results = [
            1 => ['option_id' => 1, 'points' => 6, 'rankings' => [3, 0, 0]],
            2 => ['option_id' => 2, 'points' => 3, 'rankings' => [0, 2, 1]],
            3 => ['option_id' => 3, 'points' => 0, 'rankings' => [0, 1, 2]],
        ];
        $service = new VotingCalculationService();

        // Act: Get the winning option
        $winner = $service->getWinningOption($results);

        // Assert: The correct winning option is returned
        $this->assertIsArray($winner);
        $this->assertEquals(1, $winner['option_id']);
    }

    // Test case: getWinningOption method with empty results
    public function test_get_winning_option_with_empty_results(): void
    {
        // Arrange: Set up empty results
        $results = [];
        $service = new VotingCalculationService();

        // Act: Get the winning option
        $winner = $service->getWinningOption($results);

        // Assert: Null is returned for empty results
        $this->assertNull($winner);
    }

    // Test case: handleTieBreak method with a tie
    public function test_handle_tie_break_with_tie(): void
    {
        // Arrange: Set up results with a tie in points
        $results = [
            1 => ['option_id' => 1, 'points' => 3, 'rankings' => [1, 1, 0]], // 1 first place
            2 => ['option_id' => 2, 'points' => 3, 'rankings' => [1, 1, 0]], // 1 first place
            3 => ['option_id' => 3, 'points' => 0, 'rankings' => [0, 0, 2]],
        ];
        $service = new VotingCalculationService();

        // Act: Handle the tie break
        $sortedResults = $service->handleTieBreak($results);

        // Assert: The tie is broken correctly (based on first-place votes, then option ID)
        $this->assertIsArray($sortedResults);
        $this->assertCount(3, $sortedResults);

        $sortedOptionIds = array_keys($sortedResults);
        $this->assertEquals([1, 2, 3], $sortedOptionIds); // Option 1 should come before Option 2 due to lower ID
    }

    // Test case: handleTieBreak method with no tie
    public function test_handle_tie_break_with_no_tie(): void
    {
        // Arrange: Set up results with no tie in points
        $results = [
            1 => ['option_id' => 1, 'points' => 6, 'rankings' => [3, 0, 0]],
            2 => ['option_id' => 2, 'points' => 3, 'rankings' => [0, 2, 1]],
            3 => ['option_id' => 3, 'points' => 0, 'rankings' => [0, 1, 2]],
        ];
        $service = new VotingCalculationService();

        // Act: Handle the tie break
        $sortedResults = $service->handleTieBreak($results);

        // Assert: The order remains the same as there was no tie
        $this->assertIsArray($sortedResults);
        $this->assertCount(3, $sortedResults);

        $sortedOptionIds = array_keys($sortedResults);
        $this->assertEquals([1, 2, 3], $sortedOptionIds);
    }

    // Test case: calculateResults with a large dataset (integration-style test)
    public function test_calculate_results_with_large_dataset(): void
    {
        // Arrange: Set up a large number of voters and options.
        $numVoters = 1000;
        $numOptions = 10;

        $options = new EloquentCollection();
        for ($i = 1; $i <= $numOptions; $i++) {
            $options->push((object)['id' => $i, 'name' => 'Option ' . $i, 'min_fare_per_km' => $i, 'avg_fare_per_km' => $i+1, 'max_fare_per_km' => $i+2]);
        }

        $votes = new EloquentCollection();
        for ($i = 0; $i < $numVoters; $i++) {
            $shuffledOptions = $options->shuffle();
            $rankedOptions = $shuffledOptions->pluck('id')->toArray();
            $votes->push((object)['ranked_options' => $rankedOptions]);
        }

        $votingRoundId = 1;
        $votingRoundMock = Mockery::mock('overload:' . VotingRound::class);
        $votingRoundMock->shouldReceive('with')->with(['votingOptions', 'votes'])->andReturn($votingRoundMock);
        $votingRoundMock->shouldReceive('findOrFail')->with($votingRoundId)->andReturn($votingRoundMock);
        $votingRoundMock->votingOptions = $options;
        $votingRoundMock->votes = $votes;

        $service = new VotingCalculationService();

        // Act: Perform the Borda count calculation.
        $startTime = microtime(true);
        $results = $service->calculateResults($votingRoundId);
        $endTime = microtime(true);

        // Assert: The calculation completes within an acceptable time frame (consider performance implications).
        $this->assertLessThan(1.0, $endTime - $startTime); // Expect it to be less than 1 second
        // Assert: The calculated scores and winner are correct for a large dataset (spot check or verify logic).
        $this->assertNotNull($results);
        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertArrayHasKey('points', reset($results));
        $this->assertArrayHasKey('rankings', reset($results));
    }
}
