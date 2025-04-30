<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DeliveryStatusService;
use App\Services\NotificationServiceInterface;
use App\Models\Delivery;
use Mockery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_status_updates_simple_delivery_status(): void
    {
        /** @var Delivery|Mockery\MockInterface $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->makePartial();
        $deliveryMock->shouldReceive('getAttribute')->with('stages')->andReturn([]);
        $deliveryMock->shouldReceive('update')->once()->with([
            'status' => 'in_transit',
            'current_position' => ['lat' => 1.23, 'lng' => 4.56]
        ]);
        $deliveryMock->shouldReceive('getAttribute')->with('customer_id')->andReturn('customer-456');
        $deliveryMock->shouldReceive('getAttribute')->with('id')->andReturn('delivery-789');

        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $notificationServiceMock->shouldReceive('createDeliveryNotification')
            ->once()
            ->with('customer-456', 'delivery_updated', Mockery::any());

        $service = new DeliveryStatusService($notificationServiceMock);
        $data = [
            'status' => 'in_transit',
            'current_position' => ['lat' => 1.23, 'lng' => 4.56]
        ];

        $result = $service->updateStatus($deliveryMock, $data);
        $this->assertEquals($deliveryMock, $result);
    }

    public function test_update_status_throws_exception_for_invalid_status(): void
    {
        /** @var Delivery|Mockery\MockInterface $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->makePartial();
        $deliveryMock->shouldNotReceive('update');
        $deliveryMock->shouldNotReceive('getAttribute');

        $notificationServiceMock = Mockery::mock(NotificationServiceInterface::class);
        $service = new DeliveryStatusService($notificationServiceMock);
        $data = [
            'status' => 'invalid_status',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status');
        $service->updateStatus($deliveryMock, $data);
    }
}
