<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Node;
use App\Models\Region;
use Illuminate\Database\QueryException; // Import for catching database exceptions

class NodeRegionTest extends TestCase
    {
    use RefreshDatabase;

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
}
