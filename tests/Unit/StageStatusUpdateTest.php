<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\DeliveryStatusService;
use App\Services\NotificationServiceInterface;
use App\Models\Delivery;
use App\Models\DeliveryAssignment;
use App\Models\Notification;
use Mockery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Facade;

class StageStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $deliveryAssignmentMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        Facade::clearResolvedInstances();

        $this->deliveryAssignmentMock = Mockery::mock(DeliveryAssignment::class);
        $this->app->instance(DeliveryAssignment::class, $this->deliveryAssignmentMock);

    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_update_status_updates_hybrid_delivery_stage_status(): void
    {
        /** @var Delivery|Mockery\MockInterface $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->makePartial();
        $deliveryMock->shouldReceive('getAttribute')->with('stages')->andReturn([
            ['type' => 'delivery_point', 'status' => 'pending'],
            ['type' => 'distribution_center', 'status' => 'pending'],
        ]);
        $deliveryMock->shouldReceive('update')->once()->with(Mockery::any());
        $deliveryMock->shouldReceive('getAttribute')->with('customer_id')->andReturn('customer-456');
        $deliveryMock->shouldReceive('getAttribute')->with('id')->andReturn('delivery-789');
        
        $mockBuilder = Mockery::mock(Builder::class);
        $this->deliveryAssignmentMock->shouldReceive('where')->andReturn($mockBuilder);
        $mockBuilder->shouldReceive('where')->andReturn($mockBuilder);
        $mockBuilder->shouldReceive('exists')->andReturn(true);
        
        $mockRelation = Mockery::mock(Relation::class);
        $deliveryMock->shouldReceive('assignments')->andReturn($mockRelation);
        $mockRelation->shouldReceive('where')->andReturn(Mockery::self());
        $mockRelation->shouldReceive('update')->once()->with(Mockery::any())->andReturn(1);


        $notificationService = Mockery::mock(NotificationServiceInterface::class);
        $notificationService->shouldReceive('createDeliveryNotification')->andReturnNull();
        $service = new DeliveryStatusService($notificationService);
        $data = [
            'status' => 'collected',
            'stage_type' => 'delivery_point',
            'current_position' => ['lat' => 1.23, 'lng' => 4.56]
        ];

        $result = $service->updateStatus($deliveryMock, $data);
        $this->assertEquals($deliveryMock, $result);
    }

    public function test_update_status_throws_exception_for_invalid_stage_type(): void
    {
        /** @var Delivery|Mockery\MockInterface $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->makePartial();
        $deliveryMock->shouldReceive('getAttribute')->with('stages')->andReturn([
            ['type' => 'delivery_point', 'status' => 'pending'],
        ]);
        $deliveryMock->shouldNotReceive('update');
        $deliveryMock->shouldNotReceive('getAttribute');

        $notificationService = Mockery::mock(NotificationServiceInterface::class);
        $notificationService->shouldReceive('createDeliveryNotification')->andReturnNull();
        $service = new DeliveryStatusService($notificationService);
        $data = [
            'status' => 'collected',
            'stage_type' => 'invalid_stage_type',
        ];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid stage type');
        $service->updateStatus($deliveryMock, $data);
    }

    public function test_update_stage_status_sets_delivery_to_delivered_if_all_stages_complete(): void
    {
        /** @var Delivery|Mockery\MockInterface $deliveryMock */
        $deliveryMock = Mockery::mock(Delivery::class)->makePartial();
        $deliveryMock->shouldReceive('getAttribute')->with('stages')->andReturn([
            ['type' => 'delivery_point', 'status' => 'delivered'],
            ['type' => 'distribution_center', 'status' => 'pending'],
        ]);
        $deliveryMock->shouldReceive('update')->once()->with(Mockery::subset([
            'status' => 'delivered',
            'stages' => [
                ['type' => 'delivery_point', 'status' => 'delivered'],
                ['type' => 'distribution_center', 'status' => 'delivered'],
            ]
        ]));
        $deliveryMock->shouldReceive('getAttribute')->with('customer_id')->andReturn('customer-456');
        $deliveryMock->shouldReceive('getAttribute')->with('id')->andReturn('delivery-789');
        
        $mockBuilder = Mockery::mock(Builder::class);
        $this->deliveryAssignmentMock->shouldReceive('where')->andReturn($mockBuilder);
        $mockBuilder->shouldReceive('where')->andReturn($mockBuilder);
        $mockBuilder->shouldReceive('exists')->andReturn(true);
        
        $mockRelation = Mockery::mock(Relation::class);
        $deliveryMock->shouldReceive('assignments')->andReturn($mockRelation);


        $notificationService = Mockery::mock(NotificationServiceInterface::class);
        $notificationService->shouldReceive('createDeliveryNotification')->andReturnNull();
        $service = new DeliveryStatusService($notificationService);
        $data = [
            'status' => 'delivered',
            'stage_type' => 'distribution_center',
            'current_position' => ['lat' => 1.23, 'lng' => 4.56]
        ];

        $result = $service->updateStatus($deliveryMock, $data);
        $this->assertEquals($deliveryMock, $result);
    }
}
