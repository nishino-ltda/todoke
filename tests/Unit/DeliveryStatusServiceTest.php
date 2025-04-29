<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Log;

class DeliveryStatusServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->artisan('migrate:fresh');
        
        // Mock the Log facade
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
}
