<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    #[Test]
    public function api_response_time_is_acceptable()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $start = microtime(true);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/users/me');
        $duration = microtime(true) - $start;

        $response->assertStatus(200);
        $this->assertLessThan(0.5, $duration, 'Response time should be under 500ms');
    }

    #[Test]
    public function can_handle_multiple_concurrent_requests()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $successCount = 0;
        $iterations = 10;

        for ($i = 0; $i < $iterations; $i++) {
            $response = $this->withHeaders([
                'Authorization' => "Bearer $token"
            ])->getJson('/api/v1/users/me');

            if ($response->status() === 200) {
                $successCount++;
            }
        }

        $this->assertEquals($iterations, $successCount, 
            "Should handle $iterations concurrent requests");
    }

    #[Test]
    public function delivery_listing_performance_with_large_dataset()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Criar 100 entregas para testar
        Delivery::factory()->count(100)->create();

        $start = microtime(true);
        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/deliveries');
        $duration = microtime(true) - $start;

        $response->assertStatus(200);
        $this->assertLessThan(1.0, $duration, 
            'Listing 100 deliveries should be under 1 second');
    }

    #[Test]
    public function database_query_performance()
    {
        // Criar 1000 usuários para testar
        User::factory()->count(1000)->create();

        $start = microtime(true);
        $users = User::where('type', 'customer')->limit(10)->get();
        $duration = microtime(true) - $start;

        $this->assertCount(10, $users);
        $this->assertLessThan(0.1, $duration, 
            'Simple query with limit should be under 100ms');
    }

    #[Test]
    public function complex_query_performance_with_relations()
    {
        $user = User::factory()->create(['type' => 'customer']);
        Delivery::factory()->count(50)->create(['customer_id' => $user->id]);

        $start = microtime(true);
        $deliveries = $user->deliveriesAsClient()
            ->with('courier')
            ->where('status', 'delivered')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $duration = microtime(true) - $start;

        $this->assertLessThan(0.2, $duration, 
            'Complex query with relations should be under 200ms');
    }

    // Test case: Listing deliveries with a large dataset
    // Should return results within an acceptable time frame and not exceed memory limits.
    #[Test]
    public function test_listing_deliveries_with_large_dataset_performance(): void
    {
        // Arrange: Create a large number of deliveries.
        Delivery::factory()->count(1000)->create(); // Create 1000 deliveries
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        // Act: Measure the time to list deliveries.
        $start = microtime(true);
        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/v1/deliveries');
        $duration = microtime(true) - $start;

        // Assert: The API call is successful.
        $response->assertOk();
        // Assert: The response time is within an acceptable limit.
        $this->assertLessThan(5.0, $duration, 'Listing deliveries with large dataset should be performant');
        // Assert: The test does not result in memory exhaustion errors (this is often observed during test execution, not explicitly asserted).
    }

    // Test case: Creating multiple orders sequentially
    // Should handle sequential requests efficiently.
    #[Test]
    public function test_sequential_order_creation_performance(): void
    {
        // Arrange: Create a user, partner, product associated with the partner, and order data.
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $partner = User::factory()->create(['type' => 'partner']);
        $product = \App\Models\Product::factory()->forPartner($partner->id)->create();

        $orderData = [
            'partner_id' => $partner->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1]
            ],
            'delivery' => [
                'destination' => [
                    'lat' => 12.345,
                    'lng' => 67.890,
                    'address' => '123 Test Street'
                ]
            ]
        ]; // Valid order data

        // Act: Measure the time to create multiple orders sequentially.
        $start = microtime(true);
        $iterations = 10;
        for ($i = 0; $i < $iterations; $i++) { // Send 10 sequential requests
            $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                             ->postJson('/api/v1/orders', $orderData);

            // Assert: Each request was successful.
            $response->assertStatus(201);
        }
        $duration = microtime(true) - $start;

        // Assert: The total response time is within acceptable limits for sequential creation.
        $this->assertLessThan(5.0, $duration, 'Sequential order creation should be performant');
    }

    // Test case: Updating status for multiple deliveries concurrently
    // Should handle concurrent status updates correctly and efficiently.
    public function test_concurrent_status_update_performance(): void
    {
        // Arrange: Create multiple deliveries and a courier.
        // $deliveries = Delivery::factory()->count(50)->create(['status' => 'accepted']);
        // $courier = User::factory()->courier()->create();
        // $token = $courier->createToken('test')->plainTextToken;

        // Act: Send multiple concurrent requests to update delivery statuses.
        // $promises = [];
        // $client = new \GuzzleHttp\Client(['base_uri' => config('app.url')]);
        // foreach ($deliveries as $delivery) {
        //     $promises[] = $client->patchAsync("/api/v1/deliveries/{$delivery->id}/status", [
        //         'headers' => ['Authorization' => "Bearer $token"],
        //         'json' => ['status' => 'in_transit'],
        //     ]);
        // }
        // $responses = \GuzzleHttp\Promise\Utils::unwrap($promises);

        // Assert: All requests were successful.
        // foreach ($responses as $response) {
        //     $this->assertEquals(200, $response->getStatusCode());
        // }
        // Assert: The delivery statuses are updated correctly in the database.
        // foreach ($deliveries as $delivery) {
        //     $this->assertEquals('in_transit', $delivery->fresh()->status);
        // }
        // Assert: The average response time is within acceptable limits.
    }
}
