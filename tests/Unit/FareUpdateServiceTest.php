<?php

namespace Tests\Unit;

use Tests\TestCase; // Change to extend Laravel's TestCase
use App\Models\Region;
use App\Services\FareUpdateService;
use Illuminate\Foundation\Testing\RefreshDatabase; // Use RefreshDatabase trait
use Mockery;

// Test suite for the FareUpdateService
class FareUpdateServiceTest extends TestCase
{
    use RefreshDatabase; // Use RefreshDatabase trait

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    // Test case: Updating a single region's pricing
    // Should update the community_min_fare_per_km, community_avg_fare_per_km, and community_max_fare_per_km for the specified region.
    public function test_updates_single_region_pricing(): void
    {
        // Arrange: Set up a region with initial pricing.
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 1.0,
            'community_avg_fare_per_km' => 1.5,
            'community_max_fare_per_km' => 0.0, // No max fare for this test
        ]);

        $service = new FareUpdateService();

        $newMinFare = 1.1;
        $newAvgFare = 1.6;
        $newMaxFare = 2.1;

        // Act: Update the region's pricing.
        $updatedRegion = $service->updateRegionPricing(
            $region->id,
            $newMinFare,
            $newAvgFare,
            $newMaxFare
        );

        // Assert: The regional pricing attributes are updated correctly.
        $this->assertEquals($newMinFare, $updatedRegion->fresh()->community_min_fare_per_km);
        $this->assertEquals($newAvgFare, $updatedRegion->fresh()->community_avg_fare_per_km);
        $this->assertEquals($newMaxFare, $updatedRegion->fresh()->community_max_fare_per_km);
    }

    // Test case: Calculating delivery fare with default parameters
    // Should calculate the fare based on the region's average fare and distance.
    public function test_calculates_delivery_fare_with_defaults(): void
    {
        // Arrange: Set up a region with community pricing.
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 1.0,
            'community_avg_fare_per_km' => 1.5,
            'community_max_fare_per_km' => 0.0, // No max fare for this test
        ]);

        $service = new FareUpdateService();
        $distanceKm = 10.0;

        // Act: Calculate the delivery fare.
        $fareDetails = $service->calculateDeliveryFare($region->id, $distanceKm);

        // Assert: The calculated fare is correct based on default parameters.
        $expectedTotalFare = 1.5 * 10.0; // avg_fare_per_km * distance
        $this->assertEquals($expectedTotalFare, $fareDetails['total_fare']);
        $this->assertEquals(1.5, $fareDetails['base_fare_per_km']);
        $this->assertEquals(1.5, $fareDetails['adjusted_fare_per_km']);
        $this->assertEquals(10.0, $fareDetails['distance_km']);
        $this->assertEquals(1.0, $fareDetails['time_multiplier']);
        $this->assertEquals(1.0, $fareDetails['demand_factor']);
    }

    // Test case: Calculating delivery fare with time of day adjustment
    // Should apply the correct time of day multiplier.
    public function test_calculates_delivery_fare_with_time_of_day(): void
    {
        // Arrange: Set up a region with community pricing.
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 1.0,
            'community_avg_fare_per_km' => 1.5,
            'community_max_fare_per_km' => 0.0, // No max fare for this test
        ]);

        $service = new FareUpdateService();
        $distanceKm = 10.0;
        $timeOfDay = 'night'; // Multiplier should be 1.2

        // Act: Calculate the delivery fare.
        $fareDetails = $service->calculateDeliveryFare($region->id, $distanceKm, $timeOfDay);

        // Assert: The calculated fare is correct with time of day adjustment.
        $expectedAdjustedFarePerKm = 1.5 * 1.2;
        $expectedTotalFare = $expectedAdjustedFarePerKm * 10.0;
        $this->assertEquals($expectedTotalFare, $fareDetails['total_fare']);
        $this->assertEquals(1.5, $fareDetails['base_fare_per_km']);
        $this->assertEquals($expectedAdjustedFarePerKm, $fareDetails['adjusted_fare_per_km']);
        $this->assertEquals(10.0, $fareDetails['distance_km']);
        $this->assertEquals(1.2, $fareDetails['time_multiplier']);
        $this->assertEquals(1.0, $fareDetails['demand_factor']);
    }

    // Test case: Calculating delivery fare with demand factor adjustment
    // Should apply the correct demand factor.
    public function test_calculates_delivery_fare_with_demand_factor(): void
    {
        // Arrange: Set up a region with community pricing.
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 1.0,
            'community_avg_fare_per_km' => 1.5,
            'community_max_fare_per_km' => 0.0, // No max fare for this test
        ]);

        $service = new FareUpdateService();
        $distanceKm = 10.0;
        $demandFactor = 1.5;

        // Act: Calculate the delivery fare.
        $fareDetails = $service->calculateDeliveryFare($region->id, $distanceKm, 'normal', $demandFactor);

        // Debug output
        dump([
            'expected' => 1.5 * 1.5 * 10.0,
            'actual' => $fareDetails['total_fare'],
            'details' => $fareDetails
        ]);
        
        // Assert: The calculated fare is correct with demand factor adjustment.
        $expectedAdjustedFarePerKm = 1.5 * 1.5;
        $expectedTotalFare = $expectedAdjustedFarePerKm * 10.0;
        $this->assertEquals($expectedTotalFare, $fareDetails['total_fare']);
        $this->assertEquals(1.5, $fareDetails['base_fare_per_km']);
        $this->assertEquals($expectedAdjustedFarePerKm, $fareDetails['adjusted_fare_per_km']);
        $this->assertEquals(10.0, $fareDetails['distance_km']);
        $this->assertEquals(1.0, $fareDetails['time_multiplier']);
        $this->assertEquals(1.5, $fareDetails['demand_factor']);
    }

    // Test case: Calculating delivery fare with minimum fare applied
    // Should return the minimum fare if the calculated fare is lower.
    public function test_calculates_delivery_fare_with_minimum_fare(): void
    {
        // Arrange: Set up a region with community pricing where min fare is significant.
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 2.0, // Higher min fare per km
            'community_avg_fare_per_km' => 1.5,
            'community_max_fare_per_km' => 3.0,
        ]);

        $service = new FareUpdateService();
        $distanceKm = 2.0; // Short distance

        // Act: Calculate the delivery fare.
        $fareDetails = $service->calculateDeliveryFare($region->id, $distanceKm);

        // Assert: The minimum fare is applied.
        $expectedMinFare = 2.0 * 2.0; // min_fare_per_km * distance
        $this->assertEquals($expectedMinFare, $fareDetails['total_fare']);
        $this->assertEquals(1.5, $fareDetails['base_fare_per_km']);
        $this->assertEquals(1.5, $fareDetails['adjusted_fare_per_km']);
        $this->assertEquals(2.0, $fareDetails['distance_km']);
        $this->assertEquals(1.0, $fareDetails['time_multiplier']);
        $this->assertEquals(1.0, $fareDetails['demand_factor']);
    }

     // Test case: Calculating delivery fare with maximum fare applied
    // Should return the maximum fare if the calculated fare is higher.
    public function test_calculates_delivery_fare_with_maximum_fare(): void
    {
        // Arrange: Set up a region with community pricing where max fare is significant.
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 1.0,
            'community_avg_fare_per_km' => 3.0, // Higher avg fare per km
            'community_max_fare_per_km' => 2.0, // Lower max fare per km
        ]);

        $service = new FareUpdateService();
        $distanceKm = 10.0;

        // Act: Calculate the delivery fare.
        $fareDetails = $service->calculateDeliveryFare($region->id, $distanceKm);

        // Assert: The maximum fare is applied.
        $expectedMaxFare = 2.0 * 10.0; // max_fare_per_km * distance
        $this->assertEquals($expectedMaxFare, $fareDetails['total_fare']);
        $this->assertEquals(3.0, $fareDetails['base_fare_per_km']);
        $this->assertEquals(3.0, $fareDetails['adjusted_fare_per_km']);
        $this->assertEquals(10.0, $fareDetails['distance_km']);
        $this->assertEquals(1.0, $fareDetails['time_multiplier']);
        $this->assertEquals(1.0, $fareDetails['demand_factor']);
    }

    // Test case: Calculating delivery fare with zero distance
    // Should return a fare of 0 or the minimum fare if applicable.
    public function test_calculates_delivery_fare_with_zero_distance(): void
    {
        // Arrange: Set up a region with community pricing.
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 1.0,
            'community_avg_fare_per_km' => 1.5,
            'community_max_fare_per_km' => 2.0,
        ]);

        $service = new FareUpdateService();
        $distanceKm = 0.0;

        // Act: Calculate the delivery fare.
        $fareDetails = $service->calculateDeliveryFare($region->id, $distanceKm);

        // Assert: The calculated fare is 0.
        $this->assertEquals(0.0, $fareDetails['total_fare']);
        $this->assertEquals(1.5, $fareDetails['base_fare_per_km']);
        $this->assertEquals(1.5, $fareDetails['adjusted_fare_per_km']);
        $this->assertEquals(0.0, $fareDetails['distance_km']);
        $this->assertEquals(1.0, $fareDetails['time_multiplier']);
        $this->assertEquals(1.0, $fareDetails['demand_factor']);
    }

    // Test case: Calculating delivery fare with a region having no community pricing set
    // Should use a default base fare.
    public function test_calculates_delivery_fare_with_no_community_pricing(): void
    {
        // Arrange: Set up a region with default community pricing (0.00)
        $region = Region::factory()->create([
            'community_min_fare_per_km' => 0.00,
            'community_avg_fare_per_km' => 0.00,
            'community_max_fare_per_km' => 0.00,
        ]);

        $service = new FareUpdateService();
        $distanceKm = 10.0;

        // Act: Calculate the delivery fare.
        $fareDetails = $service->calculateDeliveryFare($region->id, $distanceKm);

        // Assert: The default base fare is used (1.5 when avg is 0)
        $expectedTotalFare = 1.5 * 10.0; // Default avg_fare_per_km * distance
        $this->assertEquals($expectedTotalFare, $fareDetails['total_fare']);
        $this->assertEquals(1.5, $fareDetails['base_fare_per_km']); // Default base fare
        $this->assertEquals(1.5, $fareDetails['adjusted_fare_per_km']);
        $this->assertEquals(10.0, $fareDetails['distance_km']);
        $this->assertEquals(1.0, $fareDetails['time_multiplier']);
        $this->assertEquals(1.0, $fareDetails['demand_factor']);
    }
}
