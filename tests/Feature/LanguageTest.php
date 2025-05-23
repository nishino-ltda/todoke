<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_locale()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->patchJson('/api/v1/users/locale', [
            'locale' => 'pt-BR'
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Locale updated successfully',
            'locale' => 'pt-BR'
        ]);

        $this->assertEquals('pt-BR', $user->fresh()->locale);
    }

    public function test_locale_must_be_valid()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->patchJson('/api/v1/users/locale', [
            'locale' => 'invalid'
        ]);

        $response->assertStatus(422);
    }
}
