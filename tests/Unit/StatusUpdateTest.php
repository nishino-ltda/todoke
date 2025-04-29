<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DeliveryStatusService;
use App\Models\Delivery;
use App\Models\Notification;
use Mockery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
        $this->withoutMiddleware();
    }

    public function test_update_status_updates_simple_delivery_status(): void
    {
        $deliveryMock = Mockery::mock(Delivery::class)->shouldAllowMockingProtectedMethods();
        $deliveryMock->shouldReceive('getAttribute')->with('stages')->andReturn([]);
        $deliveryMock->shouldReceive('update')->once()->with([
            'status' => 'in_transit',
            'current_position' => ['lat' => 1.23, 'lng' => 4.56]
        ]);
        $deliveryMock->shouldReceive('getAttribute')->with('customer_id')->andReturn('customer-456');
        $deliveryMock->shouldReceive('getAttribute')->with('id')->andReturn('delivery-789');

        $notificationMock = Mockery::mock('overload:' . Notification::class);
        $notificationMock->shouldReceive('create')->once()->with(Mockery::any());

        $service = new DeliveryStatusService();
        $data = [
            'status' => 'in_transit',
            'current_position' => ['lat' => 1.23, 'lng' => 4.56]
        ];

        $result = $service->updateStatus($deliveryMock, $data);
        $this->assertEquals($deliveryMock, $result);
    }

    public function test_update_status_throws_exception_for_invalid_status(): void
    {
        $deliveryMock = Mockery::mock(Delivery::class)->shouldAllowMockingProtectedMethods();
        $deliveryMock->shouldNotReceive('update');
        $deliveryMock->shouldNotReceive('getAttribute');

        $service = new DeliveryStatusService();
        $data = [
            'status' => 'invalid_status',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status');
        $service->updateStatus($deliveryMock, $data);
    }
}
