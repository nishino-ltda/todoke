<?php

namespace Tests\Feature\Partner\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PartnerSecurityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_partner_cannot_update_unassigned_delivery(): void
    {
        $delivery = Delivery::factory()->create(['courier_id' => null]);
        $partner = User::factory()->create(['type' => 'partner']);
        $this->actingAs($partner, 'sanctum');

        $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", ['status' => 'collected']);

        $response->assertStatus(403);
    }
}
