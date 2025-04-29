<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DeliveryStatusService;
use App\Models\Delivery;
use App\Models\DeliveryAssignment;
use App\Models\Notification;
use Mockery;
use Illuminate\Support\Facades\Log;

class DeliveryAcceptanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('migrate:fresh');
        
        $logMock = Mockery::mock('alias:' . Log::class);
        $logMock->shouldReceive('debug')->withAnyArgs()->andReturn(null);
        $logMock->shouldReceive('info')->withAnyArgs()->andReturn(null);
        $logMock->shouldReceive('error')->withAnyArgs()->andReturn(null);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_accept_delivery_accepts_pending_delivery(): void
    {
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

        $notificationMock = Mockery::mock('overload:' . Notification::class);
        $notificationMock->shouldReceive('create')->once()->with([
            'user_id' => 'customer-456',
            'type' => 'delivery_updated',
            'data' => [
                'delivery_id' => 'delivery-789',
                'status' => 'accepted',
                'message' => 'Your delivery has been accepted by a courier'
            ]
        ]);

        $service = new DeliveryStatusService();
        $result = $service->acceptDelivery($deliveryMock, 'courier-123');
        $this->assertEquals($deliveryMock, $result);
    }

    public function test_accept_delivery_throws_exception_if_already_accepted(): void
    {
        $deliveryMock = Mockery::mock(Delivery::class)->shouldAllowMockingProtectedMethods();
        $deliveryMock->shouldReceive('setAttribute')->with('status', 'accepted');
        $deliveryMock->shouldReceive('getAttribute')->with('status')->andReturn('accepted');
        $deliveryMock->status = 'accepted';
        $deliveryMock->shouldNotReceive('update');
        $deliveryMock->shouldNotReceive('getAttribute');

        $service = new DeliveryStatusService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Delivery has already been accepted');
        $service->acceptDelivery($deliveryMock, 'courier-123');
    }

    public function test_accept_delivery_creates_assignments_for_hybrid_delivery(): void
    {
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

        $deliveryAssignmentAliasMock = Mockery::mock('alias:' . DeliveryAssignment::class);
        $deliveryAssignmentAliasMock->shouldReceive('updateOrCreate')
            ->once()
            ->with([
                'delivery_id' => 'delivery-789',
                'stage' => 1
            ], [
                'partner_id' => 'partner-abc',
                'status' => 'pending'
            ]);
        $deliveryAssignmentAliasMock->shouldReceive('updateOrCreate')
            ->once()
            ->with([
                'delivery_id' => 'delivery-789',
                'stage' => 2
            ], [
                'partner_id' => 'partner-xyz',
                'status' => 'pending'
            ]);

        $notificationMock = Mockery::mock('overload:' . Notification::class);
        $notificationMock->shouldReceive('create')->once()->with(Mockery::any());

        $service = new DeliveryStatusService();
        $result = $service->acceptDelivery($deliveryMock, 'courier-123');
        $this->assertEquals($deliveryMock, $result);
    }
}
