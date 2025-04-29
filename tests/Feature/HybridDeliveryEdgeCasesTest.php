<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Delivery;
use Mockery;
use App\Models\DeliveryAssignment;
use App\Models\User;
use App\Models\Node;
use App\Models\Region;
use App\Services\DeliveryStatusService;
use Illuminate\Testing\Fluent\AssertableJson; // Import for API response assertions

class HybridDeliveryEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Test handling of first stage cancellation in a hybrid delivery.
     *
     * @return void
     */
    public function test_first_stage_cancellation_handling()
    {
        $this->withoutExceptionHandling();

        // Create and authenticate as courier
        $courier = User::factory()->create(['type' => 'courier']);
        $this->actingAs($courier, 'sanctum');

        // Create logistics partner
        $partner = User::factory()->create(['type' => 'partner']);
        $region = Region::factory()->create();
        $node1 = Node::factory()->create(['region_id' => $region->id, 'type' => 'delivery_point']); // Motorbike node
        $node2 = Node::factory()->create(['region_id' => $region->id, 'type' => 'distribution_center']); // Drone node

        // Create a hybrid delivery with two stages
        $delivery = Delivery::factory()->create([
            'logistics_partner_id' => $partner->id,
            'courier_id' => $courier->id, // Assign the authenticated courier
            'is_hybrid' => true,
            'stages' => [
                ['type' => 'delivery_point', 'status' => 'pending', 'partner_id' => null, 'node_id' => $node1->id],
                ['type' => 'distribution_center', 'status' => 'pending', 'partner_id' => null, 'node_id' => $node2->id],
            ],
        ]);

        // Assume assignments are created automatically on delivery creation
        $assignment1 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 0)->first();
        $assignment2 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 1)->first();

        $this->assertNotNull($assignment1);
        $this->assertNotNull($assignment2);
        $this->assertEquals('pending', $assignment1->status);
        $this->assertEquals('pending', $assignment2->status);
        $this->assertEquals('pending', $delivery->status);

        // Simulate cancellation of the first stage (delivery_point) via API call
        // Assuming an API endpoint like PUT /api/deliveries/{delivery}/status
            $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'status' => 'canceled',
            'stage_type' => 'delivery_point'
        ]);

        $response->assertOk(); // Assert the API call was successful

        // Refresh the delivery and assignments
        $delivery->refresh();
        $assignment1->refresh();
        $assignment2->refresh();

        // Assert that the first stage and its assignment are cancelled
        $this->assertEquals('canceled', $delivery->stages[0]['status']);
        $this->assertEquals('canceled', $assignment1->status); // Service now updates assignment status

        // Assert that the second stage and its assignment are also cancelled (cascading cancellation)
        // Based on the DeliveryStatusService logic, cancelling one stage should cancel the whole delivery
        $this->assertEquals('canceled', $delivery->stages[1]['status']);
        $this->assertEquals('canceled', $assignment2->status);

        // Assert that the overall delivery status is cancelled
        $this->assertEquals('canceled', $delivery->status);

        // Assert the structure of the JSON response
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message')
                 ->where('delivery.id', $delivery->id)
                 ->where('delivery.status', 'canceled')
                 ->etc()
        );
    }

    // Test case: Cancellation during the first stage (motorbike)
    // Should cancel the entire delivery and preserve the status of the completed first stage.
    // Assert: The delivery status is updated to 'canceled'.
    // Assert: The status of the first stage assignment is preserved (e.g., 'collected').
    // Assert: The second stage assignment (drone) is also marked as canceled or not started.
    // Assert: Relevant notifications are sent to the customer and involved partners.

    // Test case: Cancellation during the second stage (drone)
    // Should cancel the remaining part of the delivery and preserve the status of the completed first stage.
    // Assert: The delivery status is updated to 'canceled'.
    // Assert: The status of the first stage assignment is preserved (e.g., 'delivered_to_dc').
    // Assert: The second stage assignment (drone) is marked as canceled.
    // Assert: Relevant notifications are sent.

    // Test case: Drone failure mid-route
    // Should trigger a fallback mechanism, potentially re-assigning the delivery to a motorbike courier.
    // Assert: The drone stage status is updated to indicate failure.
    // Assert: A new delivery assignment is created for a fallback courier.
    // Assert: The overall delivery status reflects the failure and re-assignment process.
    // Assert: Relevant parties are notified of the failure and the new assignment.

    // Test case: Attempting to update status of a canceled stage
    // Should prevent the update and return an error.
    // Assert: The API endpoint for status update returns an error (e.g., 400 or 422).
    // Assert: The status of the canceled stage remains unchanged.

    /**
     * Test handling of second stage cancellation in a hybrid delivery.
     *
     * @return void
     */
    public function test_second_stage_cancellation_handling()
    {
        $this->withoutExceptionHandling();

        // Create and authenticate as courier
        $courier = User::factory()->create(['type' => 'courier']);
        $this->actingAs($courier, 'sanctum');

        // Create necessary entities
        $partner = User::factory()->create(['type' => 'partner']);
        $region = Region::factory()->create();
        $node1 = Node::factory()->create(['region_id' => $region->id, 'type' => 'delivery_point']); // Motorbike node
        $node2 = Node::factory()->create(['region_id' => $region->id, 'type' => 'distribution_center']); // Drone node

        // Create a hybrid delivery with two stages, first stage completed
        $delivery = Delivery::factory()->create([
            'logistics_partner_id' => $partner->id,
            'courier_id' => $courier->id, // Assign the authenticated courier
            'is_hybrid' => true,
            'stages' => [
                ['type' => 'delivery_point', 'status' => 'delivered', 'partner_id' => null, 'node_id' => $node1->id],
                ['type' => 'distribution_center', 'status' => 'pending', 'partner_id' => null, 'node_id' => $node2->id],
            ],
        ]);

        // Assume assignments are created and first one completed
        $assignment1 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 0)->first();
        $assignment2 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 1)->first();

        $assignment1->update(['status' => 'delivered']);
        // $assignment2 remains pending

        $this->assertNotNull($assignment1);
        $this->assertNotNull($assignment2);
        $this->assertEquals('delivered', $assignment1->status);
        $this->assertEquals('pending', $assignment2->status);
        $this->assertEquals('pending', $delivery->status); // Status remains pending until first stage completes

        // Simulate cancellation of the second stage (distribution_center) via API call
            $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'status' => 'canceled',
            'stage_type' => 'distribution_center'
        ]);

        $response->assertOk(); // Assert the API call was successful

        // Refresh the delivery and assignments
        $delivery->refresh();
        $assignment1->refresh();
        $assignment2->refresh();

        // Assert that the first stage remains delivered
        $this->assertEquals('delivered', $delivery->stages[0]['status']);
        $this->assertEquals('delivered', $assignment1->status);

        // Assert that the second stage and its assignment are cancelled
        $this->assertEquals('canceled', $delivery->stages[1]['status']);
        $this->assertEquals('canceled', $assignment2->status);

        // Assert that the overall delivery status is cancelled
        $this->assertEquals('canceled', $delivery->status);

        // Assert the structure of the JSON response
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message')
                 ->where('delivery.id', $delivery->id)
                 ->where('delivery.status', 'canceled')
                 ->etc() // Allow other fields in the delivery object
        );
    }


    /**
     * Test handling of drone failures in a hybrid delivery.
     *
     * @return void
     */
    public function test_drone_failure_handling()
    {
        $this->withoutExceptionHandling();

        // Create and authenticate as courier
        $courier = User::factory()->create(['type' => 'courier']);
        $this->actingAs($courier, 'sanctum');

        // Create necessary entities
        $partner = User::factory()->create(['type' => 'partner']);
        $region = Region::factory()->create();
        $node1 = Node::factory()->create(['region_id' => $region->id, 'type' => 'delivery_point']); // Motorbike node
        $node2 = Node::factory()->create(['region_id' => $region->id, 'type' => 'distribution_center']); // Drone node

        // Create a hybrid delivery with two stages
        $delivery = Delivery::factory()->create([
            'logistics_partner_id' => $partner->id,
            'courier_id' => $courier->id, // Assign the authenticated courier
            'is_hybrid' => true,
            'stages' => [
                ['type' => 'delivery_point', 'status' => 'delivered', 'partner_id' => null, 'node_id' => $node1->id], // First stage delivered
                ['type' => 'distribution_center', 'status' => 'in_transit', 'partner_id' => null, 'node_id' => $node2->id], // Drone stage in transit
            ],
        ]);

        // Assume assignments are created and first one completed
        $assignment1 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 0)->first();
        $assignment2 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 1)->first();

        $assignment1->update(['status' => 'delivered']);
        $assignment2->update(['status' => 'in_transit']);

        $this->assertEquals('delivered', $assignment1->status);
        $this->assertEquals('in_transit', $assignment2->status);
        $this->assertEquals('pending', $delivery->status); // Status remains pending until first stage completes

        // Simulate drone failure during the second stage (drone stage) via API call
        // Assuming an API endpoint like PUT /api/deliveries/{delivery}/status
            $response = $this->patchJson("/api/v1/deliveries/{$delivery->id}/status", [
            'status' => 'drone_returned', // Assuming 'drone_returned' signifies a failure or return to base
            'stage_type' => 'distribution_center' // Target the drone stage
        ]);

        $response->assertOk(); // Assert the API call was successful

        // Refresh the delivery and assignments
        $delivery->refresh();
        $assignment1->refresh();
        $assignment2->refresh();

        // Assert that the drone stage status is updated to reflect failure/return
        $this->assertEquals('drone_returned', $delivery->stages[1]['status']);
        // Assuming 'drone_returned' status for the stage maps to 'failed' or a similar status for the assignment
        $this->assertEquals('failed', $assignment2->status); // Assert assignment status is 'failed'

        // Assert that the overall delivery status is updated (e.g., back to in_transit or a new failure status)
        // Based on DeliveryStatusService, drone statuses might keep overall status as in_transit unless all stages complete or a final failure state is reached.
        // Let's assume for this test that a drone_returned status sets the overall delivery status to 'failed'.
        $this->assertEquals('failed', $delivery->status); // Assert overall delivery status is 'failed'

        // TODO: Add assertions for any fallback mechanisms or notifications triggered by drone failure.
        // This might require mocking or further implementation of the service logic.

        // Assert the structure of the JSON response
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message')
                 ->where('delivery.id', $delivery->id)
                 ->where('delivery.status', 'failed') // Assert the status in the response JSON
                 ->etc() // Allow other fields in the delivery object
        );
    }

    /**
     * Test handling of offline scenarios during a hybrid delivery.
     *
     * @return void
     */
    public function test_offline_scenario_handling()
    {
        $this->withoutExceptionHandling();

        // TODO: Implementing a true offline scenario test requires mocking
        // network requests and potentially the queue system.
        // This is a placeholder for where that test would go.

        $this->markTestIncomplete('Offline scenario testing requires mocking network and queue systems.');

        /*
        // Example of what the test might involve:
        // Create necessary entities and a hybrid delivery
        $partner = User::factory()->create(['type' => 'partner']);
        $region = Region::factory()->create();
        $node1 = Node::factory()->create(['region_id' => $region->id, 'type' => 'delivery_point']); // Motorbike node
        $node2 = Node::factory()->create(['region_id' => $region->id, 'type' => 'distribution_center']); // Drone node

        $delivery = Delivery::factory()->create([
            'logistics_partner_id' => $partner->id,
            'stages' => [
                ['type' => 'delivery_point', 'status' => 'completed', 'partner_id' => null, 'node_id' => $node1->id], // First stage completed
                ['type' => 'distribution_center', 'status' => 'in_transit', 'partner_id' => null, 'node_id' => $node2->id], // Drone stage in transit
            ],
        ]);

        // Assume assignments are created and first one completed
        $assignment1 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 0)->first();
        $assignment2 = DeliveryAssignment::where('delivery_id', $delivery->id)->where('stage', 1)->first();

        $assignment1->update(['status' => 'completed']);
        $assignment2->update(['status' => 'in_transit']);

        // Simulate offline condition (e.g., mock HTTP client to fail or use a testing helper)
        // Mock the DeliveryStatusService or relevant components to simulate offline behavior

        // Attempt to update status while offline (e.g., 'drone_arrived') via API call
        // $response = $this->putJson("/api/deliveries/{$delivery->id}/status", [
        //     'status' => 'drone_arrived',
        //     'stage_type' => 'distribution_center'
        // ]);

        // Assert expected offline handling (e.g., the API call might fail, or a job is queued)
        // $response->assertStatus(503); // Example: Service Unavailable if API fails directly
        // Assert that a job was pushed to the queue (requires Queue::fake() or similar)

        // Simulate going back online (e.g., un-mock the HTTP client or process the fake queue)

        // Assert that the queued updates are processed and the delivery/assignment statuses are eventually updated correctly
        // $delivery->refresh();
        // $assignment2->refresh();
        // $this->assertEquals('drone_arrived', $delivery->stages[1]['status']);
        // $this->assertEquals('completed', $assignment2->status); // Assuming drone_arrived maps to completed assignment status
        // $this->assertEquals('delivered', $delivery->status); // Assuming all stages completed results in 'delivered'
        */
    }
}
