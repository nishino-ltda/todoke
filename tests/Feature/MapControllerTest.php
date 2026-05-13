<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\GeocodingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class MapControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();

        $this->user = User::factory()->create([
            'type' => 'customer',
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_geocode_endpoint()
    {
        $mockService = Mockery::mock(GeocodingService::class);
        $mockService->shouldReceive('geocode')
            ->with('Av Paulista, 1000')
            ->once()
            ->andReturn([
                'address' => 'Av Paulista, 1000, São Paulo, Brazil',
                'lat' => -23.5614,
                'lng' => -46.6559,
            ]);

        $this->app->instance(GeocodingService::class, $mockService);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/map/geocode?address=Av Paulista, 1000');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'address' => 'Av Paulista, 1000, São Paulo, Brazil',
                    'lat' => -23.5614,
                    'lng' => -46.6559,
                ]
            ]);
    }

    public function test_geocode_requires_address()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/map/geocode');

        $response->assertStatus(422);
    }

    public function test_reverse_geocode_endpoint()
    {
        $mockService = Mockery::mock(GeocodingService::class);
        $mockService->shouldReceive('reverseGeocode')
            ->with(-23.5505, -46.6333)
            ->once()
            ->andReturn([
                'address' => 'Praça da Sé, São Paulo, Brazil',
                'lat' => -23.5505,
                'lng' => -46.6333,
            ]);

        $this->app->instance(GeocodingService::class, $mockService);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/map/reverse-geocode?lat=-23.5505&lng=-46.6333');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'address' => 'Praça da Sé, São Paulo, Brazil',
                    'lat' => -23.5505,
                    'lng' => -46.6333,
                ]
            ]);
    }

    public function test_distance_endpoint()
    {
        $mockService = Mockery::mock(GeocodingService::class);
        $mockService->shouldReceive('getDistance')
            ->once()
            ->andReturn([
                'distance_km' => 2.5,
                'duration_seconds' => 600,
            ]);

        $this->app->instance(GeocodingService::class, $mockService);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/map/distance?origin_lat=-23.5505&origin_lng=-46.6333&dest_lat=-23.5614&dest_lng=-46.6559');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'distance_km' => 2.5,
                    'duration_seconds' => 600,
                ]
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
