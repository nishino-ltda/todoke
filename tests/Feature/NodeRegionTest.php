<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Node;
use App\Models\Region;

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
     * Test that deleting a region does not delete associated nodes (or handles it as per foreign key constraints).
     *
     * @return void
     */
    public function test_deleting_region_handles_associated_nodes()
    {
        $region = Region::factory()->create();
        $node = Node::factory()->create(['region_id' => $region->id]);

        // Assuming foreign key constraint is set to SET NULL or similar
        // If it's RESTRICT or CASCADE, the test would need to be adjusted
        $region->delete();

        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
        
        // Check if the node's region_id is set to null or if the node was deleted (depending on constraint)
        // For SET NULL:
        $this->assertDatabaseHas('nodes', ['id' => $node->id, 'region_id' => null]);
        
        // For CASCADE:
        // $this->assertDatabaseMissing('nodes', ['id' => $node->id]);
    }
}
