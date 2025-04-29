<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DeliveryAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class PartnerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    /* write new tests for the PartnerTest class */
}
