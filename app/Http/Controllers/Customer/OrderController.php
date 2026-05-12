<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Order;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('customer_id', $request->user()->id)
            ->with(['partner', 'delivery', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::where('customer_id', $request->user()->id)
            ->with(['items.product', 'delivery', 'partner'])
            ->findOrFail($id);

        return Inertia::render('Customer/Orders/Show', [
            'orderId' => (string) $id,
            'orderData' => $order
        ]);
    }
}
