<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function partnerIndex(Request $request)
    {
        $regions = Region::where('partner_id', $request->user()->id)->get();
        return response()->json($regions);
    }

    public function partnerUpdate(Request $request, $id)
    {
        $region = Region::where('partner_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'polygon' => 'sometimes|array',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $region->update($validated);

        return response()->json($region);
    }

    public function partnerDestroy(Request $request, $id)
    {
        $region = Region::where('partner_id', $request->user()->id)->findOrFail($id);
        $region->delete();

        return response()->json(null, 204);
    }

    public function index(Request $request)
    {
        // Public list or partner's list?
        // If partner, filter by partner_id
        if ($request->user()->type === 'partner') {
            return response()->json([
                'data' => Region::where('partner_id', $request->user()->id)->get()
            ]);
        }
        
        return response()->json([
            'data' => Region::all()
        ]);
    }

    public function adminIndex()
    {
        return response()->json([
            'data' => Region::with('partner')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'polygon' => 'required|array',
            'partner_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $region = Region::create([
            'name' => $validated['name'],
            'polygon' => $validated['polygon'],
            'partner_id' => $validated['partner_id'] ?? $request->user()->id,
            'status' => $validated['status'] ?? 'active'
        ]);

        return response()->json([
            'data' => $region
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $region = Region::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'polygon' => 'sometimes|array',
            'partner_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $region->update($validated);

        return response()->json([
            'data' => $region
        ]);
    }

    public function destroy($id)
    {
        $region = Region::findOrFail($id);
        $region->delete();

        return response()->json(null, 204);
    }
}
