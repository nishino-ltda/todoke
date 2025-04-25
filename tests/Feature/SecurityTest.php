<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/v1/users/me');
        $response->assertStatus(401);
    }

    /** @test */
    public function users_cannot_access_admin_routes()
    {
        $user = User::factory()->create(['type' => 'customer']);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['type' => 'admin']);
        $token = $admin->createToken('test', ['admin'])->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/admin/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function invalid_json_input_is_rejected()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Content-Type' => 'application/json'
        ])->postJson('/api/v1/deliveries', [
            'origem' => ["lat" => "invalid", "lng" => "invalid"], // Tipos inválidos
            'destino' => null, // Valor nulo não permitido
            'item_description' => 123, // Tipo numérico inválido
            'estimated_weight' => "heavy", // String inválida
            'dimensions' => "invalid" // Formato inválido
        ]);

        $response->assertStatus(422);
    }


    /** @test */
    public function rate_limiting_works_on_auth_endpoints()
    {
        $user = User::factory()->create([
            'email' => 'validuser@example.com',
            'password' => bcrypt('correctpassword')
        ]);

        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'validuser@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        $response->assertStatus(429);
    }

    /** @test */
    public function sensitive_data_is_not_exposed_in_responses()
    {
        $user = User::factory()->create([
            'password' => bcrypt('secret'),
            'remember_token' => 'test-token'
        ]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer $token"
        ])->getJson('/api/v1/users/me');

        $response->assertJsonMissing([
            'senha',
            'remember_token'
        ]);
    }

    /** @test */
    public function no_raw_sql_queries_are_used_in_codebase()
    {
        $patterns = [
            '/DB::raw\(.*\)/',
            '/->whereRaw\(.*\)/',
            '/->selectRaw\(.*\)/',
            '/->havingRaw\(.*\)/',
            '/->orderByRaw\(.*\)/',
            '/DB::select\(.*\)/',
            '/DB::statement\(.*\)/',
            '/DB::unprepared\(.*\)/',
            '/eval\(.*\)/',
            '/exec\(.*\)/',
            '/shell_exec\(.*\)/',
            '/system\(.*\)/',
            '/passthru\(.*\)/',
            '/popen\(.*\)/',
            '/proc_open\(.*\)/',
            '/assert\(.*\)/',
            '/create_function\(.*\)/'
        ];

        $violations = [];

        $files = glob(app_path('**/*.php'), GLOB_BRACE);

        foreach ($files as $fullPath) {
            if (!file_exists($fullPath)) {
                continue;
            }

            $content = file_get_contents($fullPath);
            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    foreach ($matches[0] as $match) {
                        $lineNumber = substr_count(substr($content, 0, $match[1]), "\n") + 1;
                        $relativePath = str_replace(app_path() . '/', '', $fullPath);
                        $violations[] = sprintf(
                            "Found potential security issue in %s:%d - %s",
                            $relativePath,
                            $lineNumber,
                            substr($match[0], 0, 50) . (strlen($match[0]) > 50 ? '...' : '')
                        );
                    }
                }
            }
        }

        $this->assertEmpty($violations, "Security issues found:\n" . implode("\n", $violations));
    }
}
