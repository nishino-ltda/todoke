<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/v1/users/me');

        $response->assertOk()
            ->assertJsonStructure([
                'id',
                'name', 
                'email',
                'phone',
                'type',
                'photoUrl',
                'status'
            ]);
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson('/api/v1/users/me', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'name' => 'Test User',
                'email' => 'test@example.com'
            ]);

        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->patchJson('/api/v1/users/me', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response->assertOk();
        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/v1/users/me', [
                'password' => 'Password123',
            ]);

        $response->assertNoContent();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/v1/users/me', [
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
        $this->assertNotNull($user->fresh());
    }
}
