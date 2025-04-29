<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Node;
use App\Models\Region;
use Illuminate\Database\QueryException; // Import for catching database exceptions
use Mockery;

class NodeRegionTest extends TestCase
    {
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mockery::close();
    }

    /**
     * Test that a node can be associated with a region.
     *
     * @return void
     */
    public function test_node_can_be_associated_with_region()
    {
        $region = Region::factory()->create();
        $node = Node::factory()->create(['region_id' => $region->id]);

        $this->assertEquals($region->id, $node->region_id);
        $this->assertTrue($node->region->is($region));
    }

    /**
     * Test that a node can be created without a region (if region_id is nullable).
     * Based on the migration, region_id is NOT nullable, so this test will assert it cannot be created without a region.
     *
     * @return void
     */
    public function test_node_cannot_be_created_without_region()
    {
        $this->expectException(QueryException::class); // Expect a database query exception

        // Attempt to create a node without a region_id
        Node::factory()->create(['region_id' => null]);
    }


    /**
     * Test that retrieving a region includes its associated nodes.
     *
     * @return void
     */
    public function test_region_includes_associated_nodes()
    {
        $region = Region::factory()->create();
        $node1 = Node::factory()->create(['region_id' => $region->id]);
        $node2 = Node::factory()->create(['region_id' => $region->id]);
        $node3 = Node::factory()->create(); // Node in a different region

        $retrievedRegion = Region::with('nodes')->find($region->id);

        $this->assertCount(2, $retrievedRegion->nodes);
        $this->assertTrue($retrievedRegion->nodes->contains($node1));
        $this->assertTrue($retrievedRegion->nodes->contains($node2));
        $this->assertFalse($retrievedRegion->nodes->contains($node3));
    }

    /**
     * Test that deleting a region with associated nodes is restricted.
     * Based on the foreign key constraint in the migration, deleting a region with nodes should be prevented.
     *
     * @return void
     */
    public function test_deleting_region_with_associated_nodes_is_restricted()
    {
        $region = Region::factory()->create();
        $node = Node::factory()->create(['region_id' => $region->id]);

        // Expect a QueryException because of the RESTRICT foreign key constraint
        $this->expectException(QueryException::class);

        // Attempt to delete the region
        $region->delete();

        // The following assertions should not be reached if the exception is thrown,
        // but are included for clarity on what would be asserted if the constraint was different.
        $this->assertDatabaseHas('regions', ['id' => $region->id]); // Region should still exist
        $this->assertDatabaseHas('nodes', ['id' => $node->id, 'region_id' => $region->id]); // Node should still exist and be associated
    }

    /**
     * Test that deleting a region without associated nodes is successful.
     *
     * @return void
     */
    public function test_deleting_region_without_associated_nodes_is_successful()
    {
        $region = Region::factory()->create();

        // Delete the region
        $region->delete();

        // Assert that the region is deleted from the database
        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    }

    // Test case: Creating a region with valid GeoJSON
    // Should successfully create the region and store the GeoJSON data.
    public function test_creates_region_with_valid_geojson(): void
    {
        // Arrange: Set up valid GeoJSON data for a region.
        // $validGeoJson = '{ "type": "Polygon", "coordinates": [ [ [100.0, 0.0], [101.0, 0.0], [101.0, 1.0], [100.0, 1.0], [100.0, 0.0] ] ] }';
        // $regionData = ['name' => 'Test Region', 'geojson' => $validGeoJson];

        // Act: Send a request to create the region.
        // $response = $this->postJson('/api/v1/regions', $regionData);

        // Assert: The API call is successful and the region is created.
        // $response->assertStatus(201);
        // $this->assertDatabaseHas('regions', ['name' => 'Test Region', 'geojson' => $validGeoJson]);
    }

    // Test case: Creating a region with invalid GeoJSON
    // Should reject the request and return a validation error.
    public function test_cannot_create_region_with_invalid_geojson(): void
    {
        // Arrange: Set up invalid GeoJSON data.
        // $invalidGeoJson = '{ "type": "InvalidType", "coordinates": [] }';
        // $regionData = ['name' => 'Invalid Region', 'geojson' => $invalidGeoJson];

        // Act: Send a request to create the region.
        // $response = $this->postJson('/api/v1/regions', $regionData);

        // Assert: The API endpoint returns a validation error (e.g., 422).
        // $response->assertStatus(422);
        // Assert: The response includes an error message related to the GeoJSON.
        // $response->assertJsonValidationErrors('geojson');
        // Assert: No new Region model is created.
        // $this->assertDatabaseMissing('regions', ['name' => 'Invalid Region']);
    }

    // Test case: Admin approving a pending node
    // Should change the node's status to 'active' and allow it to be assigned deliveries.
    public function test_admin_approves_pending_node(): void
    {
        // Arrange: Create a pending node and an admin user.
        // $adminUser = User::factory()->create(['type' => 'admin']);
        // $pendingNode = Node::factory()->create(['status' => 'pending']);
        // $this->actingAs($adminUser, 'sanctum');

        // Act: Send a request to approve the node.
        // Assuming an API endpoint like PATCH /api/v1/nodes/{node}/approve
        // $response = $this->patchJson("/api/v1/nodes/{$pendingNode->id}/approve");

        // Assert: The API call is successful and the node status is updated.
        // $response->assertOk();
        // $this->assertEquals('active', $pendingNode->fresh()->status);
        // Assert: The node becomes available for delivery assignments (this might require a separate test or service interaction).
    }

    // Test case: Non-admin attempting to approve a node
    // Should be denied access and return a forbidden error.
    public function test_non_admin_cannot_approve_node(): void
    {
        // Arrange: Create a pending node and a non-admin user (e.g., customer or courier).
        // $nonAdminUser = User::factory()->create(['type' => 'customer']); // Or 'courier'
        // $pendingNode = Node::factory()->create(['status' => 'pending']);
        // $this->actingAs($nonAdminUser, 'sanctum');

        // Act: Send a request to approve the node.
        // $response = $this->patchJson("/api/v1/nodes/{$pendingNode->id}/approve");

        // Assert: The API endpoint returns a forbidden error (e.g., 403).
        // $response->assertStatus(403);
        // Assert: The node's status remains unchanged.
        // $this->assertEquals('pending', $pendingNode->fresh()->status);
    }
}
