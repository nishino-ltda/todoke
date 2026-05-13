<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    protected string $nominatimUrl = 'https://nominatim.openstreetmap.org';
    protected string $deepseekUrl = 'https://api.deepseek.com/v1/chat/completions';
    protected string $osrmUrl = 'https://router.project-osrm.org/route/v1/driving';

    public function geocode(string $address): array
    {
        $result = $this->nominatimGeocode($address);

        if ($result !== null) {
            return $result;
        }

        return $this->deepseekGeocode($address);
    }

    public function reverseGeocode(float $lat, float $lng): array
    {
        $response = Http::withHeaders([
            'User-Agent' => config('services.nominatim.user_agent'),
        ])->get("{$this->nominatimUrl}/reverse", [
            'lat' => $lat,
            'lon' => $lng,
            'format' => 'json',
            'addressdetails' => 1,
        ]);

        if ($response->successful() && isset($response['display_name'])) {
            $addr = $response['address'] ?? [];
            return [
                'address' => $response['display_name'],
                'lat' => (float) $response['lat'],
                'lng' => (float) $response['lon'],
                'city' => $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? null,
                'state' => $addr['state'] ?? null,
                'neighborhood' => $addr['neighbourhood'] ?? $addr['suburb'] ?? null,
                'state_short' => $addr['ISO3166-2-lvl4'] ?? null,
            ];
        }

        return [
            'address' => "{$lat}, {$lng}",
            'lat' => $lat,
            'lng' => $lng,
        ];
    }

    public function getDistance(array $origin, array $dest): array
    {
        $originLng = $origin['lng'] ?? $origin[1] ?? 0;
        $originLat = $origin['lat'] ?? $origin[0] ?? 0;
        $destLng = $dest['lng'] ?? $dest[1] ?? 0;
        $destLat = $dest['lat'] ?? $dest[0] ?? 0;

        try {
            $response = Http::timeout(5)->get(
                "{$this->osrmUrl}/{$originLng},{$originLat};{$destLng},{$destLat}",
                ['overview' => 'false']
            );

            if ($response->successful() && isset($response['code']) && $response['code'] === 'Ok') {
                $route = $response['routes'][0] ?? null;
                if ($route) {
                    return [
                        'distance_km' => round($route['distance'] / 1000, 2),
                        'duration_seconds' => (int) $route['duration'],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('OSRM request failed, falling back to Haversine', ['error' => $e->getMessage()]);
        }

        return $this->haversineDistance($originLat, $originLng, $destLat, $destLng);
    }

    protected function nominatimGeocode(string $address): ?array
    {
        $response = Http::withHeaders([
            'User-Agent' => config('services.nominatim.user_agent'),
        ])->get("{$this->nominatimUrl}/search", [
            'q' => $address,
            'format' => 'json',
            'addressdetails' => 1,
            'limit' => 5,
        ]);

        if ($response->successful() && count($response->json()) > 0) {
            $first = $response[0];
            $addr = $first['address'] ?? [];
            return [
                'address' => $first['display_name'],
                'lat' => (float) $first['lat'],
                'lng' => (float) $first['lon'],
                'city' => $addr['city'] ?? $addr['town'] ?? $addr['village'] ?? null,
                'state' => $addr['state'] ?? null,
                'neighborhood' => $addr['neighbourhood'] ?? $addr['suburb'] ?? null,
                'state_short' => $addr['ISO3166-2-lvl4'] ?? null,
            ];
        }

        return null;
    }

    protected function deepseekGeocode(string $address): array
    {
        $apiKey = config('services.deepseek.api_key');

        if (empty($apiKey)) {
            Log::warning('DeepSeek API key not configured, returning raw address');
            return [
                'address' => $address,
                'lat' => 0,
                'lng' => 0,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(10)->post($this->deepseekUrl, [
                'model' => 'deepseek-chat',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => "Você é um assistente de geocoding. Extraia o endereço normalizado e coordenadas aproximadas do texto: \"{$address}\". Responda apenas JSON: {\"address\": \"...\", \"lat\": ..., \"lng\": ..., \"city\": \"...\", \"state\": \"...\", \"neighborhood\": \"...\"}",
                    ],
                ],
                'max_tokens' => 200,
            ]);

            if ($response->successful()) {
                $content = $response['choices'][0]['message']['content'] ?? '';
                $content = trim($content);
                $content = str_replace(['```json', '```'], '', $content);
                $data = json_decode($content, true);

                if ($data && isset($data['lat'], $data['lng'])) {
                    return [
                        'address' => $data['address'] ?? $address,
                        'lat' => (float) $data['lat'],
                        'lng' => (float) $data['lng'],
                        'city' => $data['city'] ?? null,
                        'state' => $data['state'] ?? null,
                        'neighborhood' => $data['neighborhood'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('DeepSeek geocoding failed', ['error' => $e->getMessage()]);
        }

        return [
            'address' => $address,
            'lat' => 0,
            'lng' => 0,
        ];
    }

    protected function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        $averageSpeedKmph = 30;
        $durationSeconds = $distance > 0 ? ($distance / $averageSpeedKmph) * 3600 : 0;

        return [
            'distance_km' => round($distance, 2),
            'duration_seconds' => (int) round($durationSeconds),
        ];
    }
}
