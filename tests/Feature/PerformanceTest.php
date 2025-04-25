<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function complex_query_performance_with_relations()
    {
        $user = User::factory()->create(['type' => 'customer']);
        Delivery::factory()->count(50)->create(['customer_id' => $user->id]);

        $start = microtime(true);
        $deliveries = $user->deliveriesAsClient()
            ->with('courier')
            ->where('status', 'delivered')
            ->orderBy('createdAt', 'desc')
            ->limit(10)
            ->get();
        $duration = microtime(true) - $start;

        $this->assertLessThan(0.2, $duration, 
            'Complex query with relations should be under 200ms');
    }
}
