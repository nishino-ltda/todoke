<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Http;

class GeocodingServiceTest extends TestCase
{
    private GeocodingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeocodingService();
    }

    public function test_geocode_with_nominatim_success()
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([
                [
                    'display_name' => 'Av Paulista, São Paulo, Brazil',
                    'lat' => '-23.5614',
                    'lon' => '-46.6559',
                ]
            ]),
        ]);

        $result = $this->service->geocode('Av Paulista, SP');

        $this->assertEquals('Av Paulista, São Paulo, Brazil', $result['address']);
        $this->assertEquals(-23.5614, $result['lat']);
        $this->assertEquals(-46.6559, $result['lng']);
    }

    public function test_geocode_falls_back_to_deepseek_when_nominatim_empty()
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([]),
            'api.deepseek.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '{"address": "Rua Augusta, SP", "lat": -23.55, "lng": -46.63}']]
                ]
            ]),
        ]);

        $result = $this->service->geocode('Rua Augusta, SP');

        $this->assertEquals('Rua Augusta, SP', $result['address']);
        $this->assertEquals(-23.55, $result['lat']);
        $this->assertEquals(-46.63, $result['lng']);
    }

    public function test_geocode_returns_raw_address_when_both_fail()
    {
        Http::fake([
            'nominatim.openstreetmap.org/search*' => Http::response([]),
            'api.deepseek.com/*' => Http::response([], 500),
        ]);

        $result = $this->service->geocode('Endereco desconhecido');

        $this->assertEquals('Endereco desconhecido', $result['address']);
        $this->assertEquals(0, $result['lat']);
        $this->assertEquals(0, $result['lng']);
    }

    public function test_reverse_geocode_success()
    {
        Http::fake([
            'nominatim.openstreetmap.org/reverse*' => Http::response([
                'display_name' => 'Praça da Sé, São Paulo, Brazil',
                'lat' => '-23.5505',
                'lon' => '-46.6333',
            ]),
        ]);

        $result = $this->service->reverseGeocode(-23.5505, -46.6333);

        $this->assertEquals('Praça da Sé, São Paulo, Brazil', $result['address']);
        $this->assertEquals(-23.5505, $result['lat']);
        $this->assertEquals(-46.6333, $result['lng']);
    }

    public function test_haversine_distance()
    {
        $result = $this->service->getDistance(
            ['lat' => -23.5505, 'lng' => -46.6333],
            ['lat' => -23.5614, 'lng' => -46.6559]
        );

        $this->assertArrayHasKey('distance_km', $result);
        $this->assertArrayHasKey('duration_seconds', $result);
        $this->assertGreaterThan(0, $result['distance_km']);
        $this->assertGreaterThan(0, $result['duration_seconds']);
    }

    public function test_get_distance_uses_osrm_when_available()
    {
        Http::fake([
            'router.project-osrm.org/*' => Http::response([
                'code' => 'Ok',
                'routes' => [
                    ['distance' => 2500, 'duration' => 300],
                ],
            ]),
        ]);

        $result = $this->service->getDistance(
            ['lat' => -23.5505, 'lng' => -46.6333],
            ['lat' => -23.5614, 'lng' => -46.6559]
        );

        $this->assertEquals(2.5, $result['distance_km']);
        $this->assertEquals(300, $result['duration_seconds']);
    }
}
