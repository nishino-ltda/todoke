<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the partner's orders.
     */
    public function index()
    {
        return Inertia::render('Partner/Orders/Index');
    }

    /**
     * Display the specified partner order.
     */
    public function show($orderId)
    {
        return Inertia::render('Partner/Orders/Show', [
            'orderId' => $orderId,
        ]);
    }

    /**
     * Update the status of the specified partner order.
     */
    public function updateStatus(Request $request, $orderId)
    {
        // Placeholder for status update logic
        return response()->json(['message' => 'Status updated (placeholder)']);
    }
}
