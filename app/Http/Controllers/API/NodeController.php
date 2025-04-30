<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id' => 'required|exists:users,id',
            'type' => 'required|in:partner,distribution_center,delivery_point',
            'identifier' => 'required|string|unique:nodes',
            'capacity' => 'nullable|numeric',
            'status' => 'required|in:active,inactive,maintenance,pending_approval',
            'region_id' => 'required|exists:regions,id',
            'current_position' => 'required|array'
        ]);

        $node = Node::create([
            'type' => $validated['type'],
            'identifier' => $validated['identifier'],
            'capacity' => $validated['capacity'],
            'region_id' => $validated['region_id'],
            'current_position' => [
                'lat' => $validated['current_position']['lat'],
                'lng' => $validated['current_position']['lng']
            ],
            'partner_id' => $validated['partner_id'],
            'status' => 'pending_approval'
        ]);

        return response()->json([
            'data' => $node
        ], 201);
    }

    public function approve(Node $node)
    {
        $this->authorize('approve', $node);
        
        $node->update(['status' => 'active']);
        
        return response()->json([
            'message' => 'Node approved successfully',
            'data' => $node
        ]);
    }
}
