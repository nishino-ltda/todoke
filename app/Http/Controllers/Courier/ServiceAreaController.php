<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\CourierServiceArea;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceAreaController extends Controller
{
    public function index()
    {
        $serviceArea = CourierServiceArea::where('user_id', auth()->id())->first();

        return Inertia::render('Courier/ServiceArea/Index', [
            'serviceArea' => $serviceArea
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'polygon' => 'required|array',
            'is_active' => 'boolean'
        ]);

        $serviceArea = CourierServiceArea::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'polygon' => $validated['polygon'],
                'is_active' => $validated['is_active'] ?? true,
                'name' => 'Main Service Area'
            ]
        );

        return back()->with('success', 'Service area updated successfully');
    }
}
