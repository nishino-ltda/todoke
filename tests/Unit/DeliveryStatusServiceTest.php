<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeliveryStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
        $this->withoutMiddleware();
        
        // Mock the Log facade
        $logMock = Mockery::mock('alias:' . Log::class);
        $logMock->shouldReceive('debug')->withAnyArgs()->andReturn(null);
        $logMock->shouldReceive('info')->withAnyArgs()->andReturn(null);
        $logMock->shouldReceive('error')->withAnyArgs()->andReturn(null);
    }
}
