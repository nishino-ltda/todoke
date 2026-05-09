<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DeliveryStatusService;
use App\Services\NotificationServiceInterface;
use App\Models\Delivery;
use App\Models\DeliveryAssignment;
use Mockery;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Facade;

class DeliveryAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock all facades
        Log::shouldReceive('debug')->withAnyArgs()->andReturn(null);
        Log::shouldReceive('info')->withAnyArgs()->andReturn(null);
        Log::shouldReceive('error')->withAnyArgs()->andReturn(null);
        
        // Disable middleware
        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        parent::tearDown();
    }

    public function test_accept_delivery_accepts_pending_delivery(): void
    {
        $delivery = Delivery::factory()->create([
            'status' => 'pending',
            'stages' => []
        ]);
        $courier = \App\Models\User::factory()->create();

        // Mock notification service
        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $notificationServiceMock->shouldReceive('createDeliveryNotification')
            ->once()
            ->with($delivery->customer_id, 'delivery_updated', Mockery::on(function ($data) use ($delivery) {
                return $data['delivery_id'] === $delivery->id && $data['status'] === 'accepted';
            }));

        $service = new DeliveryStatusService($notificationServiceMock);
        $result = $service->acceptDelivery($delivery, $courier->id);
        
        $this->assertEquals('accepted', $result->fresh()->status);
        $this->assertEquals($courier->id, $result->fresh()->courier_id);
    }

    public function test_accept_delivery_throws_exception_if_already_accepted(): void
    {
        $delivery = Delivery::factory()->create([
            'status' => 'accepted'
        ]);
        $courier = \App\Models\User::factory()->create();

        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $service = new DeliveryStatusService($notificationServiceMock);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery has already been accepted');
        $service->acceptDelivery($delivery, $courier->id);
    }

    public function test_accept_delivery_creates_assignments_for_hybrid_delivery(): void
    {
        $partner1 = \App\Models\User::factory()->create();
        $partner2 = \App\Models\User::factory()->create();
        
        $delivery = Delivery::factory()->create([
            'status' => 'pending',
            'stages' => [
                ['type' => 'delivery_point', 'partner_id' => $partner1->id],
                ['type' => 'distribution_center', 'partner_id' => $partner2->id],
            ]
        ]);
        $courier = \App\Models\User::factory()->create();

        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $notificationServiceMock->shouldReceive('createDeliveryNotification')
            ->once()
            ->with($delivery->customer_id, 'delivery_updated', Mockery::any());

        $service = new DeliveryStatusService($notificationServiceMock);
        $service->acceptDelivery($delivery, $courier->id);

        $this->assertEquals('accepted', $delivery->fresh()->status);
        
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'stage' => 1,
            'partner_id' => $partner1->id,
            'status' => 'pending'
        ]);
        
        $this->assertDatabaseHas('delivery_assignments', [
            'delivery_id' => $delivery->id,
            'stage' => 2,
            'partner_id' => $partner2->id,
            'status' => 'pending'
        ]);
    }
}
