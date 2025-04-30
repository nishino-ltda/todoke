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
        
        // Clear any existing mocks
        Mockery::close();
        Facade::clearResolvedInstances();
        
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
        /** @var Mockery\MockInterface|Delivery $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->shouldAllowMockingProtectedMethods();
        $deliveryMock->shouldReceive('setAttribute')->with('status', 'pending');
        $deliveryMock->shouldReceive('getAttribute')->with('status')->andReturn('pending');
        $deliveryMock->status = 'pending';
        $deliveryMock->shouldReceive('update')->once()->with([
            'courier_id' => 'courier-123',
            'status' => 'accepted'
        ]);
        $deliveryMock->shouldReceive('getAttribute')->with('stages')->andReturn([]);
        $deliveryMock->shouldReceive('getAttribute')->with('customer_id')->andReturn('customer-456');
        $deliveryMock->shouldReceive('getAttribute')->with('id')->andReturn('delivery-789');

        // Mock notification service
        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $notificationServiceMock->shouldReceive('createDeliveryNotification')
            ->once()
            ->with('customer-456', 'delivery_updated', Mockery::any());

        $service = new DeliveryStatusService($notificationServiceMock);
        $result = $service->acceptDelivery($deliveryMock, 'courier-123');
        $this->assertEquals($deliveryMock, $result);
    }

    public function test_accept_delivery_throws_exception_if_already_accepted(): void
    {
        /** @var Mockery\MockInterface|Delivery $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->shouldAllowMockingProtectedMethods();
        $deliveryMock->shouldReceive('setAttribute')->with('status', 'accepted');
        $deliveryMock->shouldReceive('getAttribute')->with('status')->andReturn('accepted');
        $deliveryMock->status = 'accepted';
        $deliveryMock->shouldNotReceive('update');
        $deliveryMock->shouldNotReceive('getAttribute');

        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $service = new DeliveryStatusService($notificationServiceMock);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery has already been accepted');
        $service->acceptDelivery($deliveryMock, 'courier-123');
    }

    public function test_accept_delivery_creates_assignments_for_hybrid_delivery(): void
    {
        /** @var Mockery\MockInterface|Delivery $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->shouldAllowMockingProtectedMethods();
        $deliveryMock->shouldReceive('setAttribute')->with('status', 'pending');
        $deliveryMock->shouldReceive('getAttribute')->with('status')->andReturn('pending');
        $deliveryMock->status = 'pending';
        $deliveryMock->shouldReceive('update')->once()->with([
            'courier_id' => 'courier-123',
            'status' => 'accepted'
        ]);
        $deliveryMock->shouldReceive('getAttribute')->with('stages')->andReturn([
            ['type' => 'delivery_point', 'partner_id' => 'partner-abc'],
            ['type' => 'distribution_center', 'partner_id' => 'partner-xyz'],
        ]);
        $deliveryMock->shouldReceive('getAttribute')->with('logistics_partner_id')->andReturn(null);
        $deliveryMock->shouldReceive('getAttribute')->with('customer_id')->andReturn('customer-456');
        $deliveryMock->shouldReceive('getAttribute')->with('id')->andReturn('delivery-789');

        $deliveryAssignmentMock = Mockery::mock('overload:' . DeliveryAssignment::class);
        $deliveryAssignmentMock->shouldReceive('updateOrCreate')
            ->once()
            ->with([
                'delivery_id' => 'delivery-789',
                'stage' => 1
            ], [
                'partner_id' => 'partner-abc',
                'status' => 'pending'
            ]);
        $deliveryAssignmentMock->shouldReceive('updateOrCreate')
            ->once()
            ->with([
                'delivery_id' => 'delivery-789',
                'stage' => 2
            ], [
                'partner_id' => 'partner-xyz',
                'status' => 'pending'
            ]);

        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $notificationServiceMock->shouldReceive('createDeliveryNotification')
            ->once()
            ->with('customer-456', 'delivery_updated', Mockery::any());

        $service = new DeliveryStatusService($notificationServiceMock);
        $result = $service->acceptDelivery($deliveryMock, 'courier-123');
        $this->assertEquals($deliveryMock, $result);
    }
}
