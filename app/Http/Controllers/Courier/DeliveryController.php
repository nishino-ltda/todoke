<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the courier's deliveries.
     */
    public function index()
    {
        return Inertia::render('Courier/Deliveries/Index');
    }

    /**
     * Display the specified delivery.
     */
    public function show($deliveryId)
    {
        return Inertia::render('Courier/Deliveries/Show', [
            'deliveryId' => $deliveryId,
        ]);
    }

    /**
     * Update the status of the specified delivery.
     */
    public function updateStatus(Request $request, $deliveryId)
    {
        // Placeholder for status update logic
        return response()->json(['message' => 'Status updated (placeholder)']);
    }

    /**
     * Display a listing of the courier's hybrid deliveries.
     */
    public function hybridIndex()
    {
        return Inertia::render('Courier/HybridDeliveries/Index');
    }

    /**
     * Display the specified hybrid delivery.
     */
    public function hybridShow($deliveryId)
    {
        return Inertia::render('Courier/HybridDeliveries/Show', [
            'deliveryId' => $deliveryId,
        ]);
    }
}
