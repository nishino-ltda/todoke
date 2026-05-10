<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    if (!$order) return false;
    return $user->id === $order->customer_id || $user->id === $order->partner_id || $user->role === 'admin';
});

Broadcast::channel('deliveries.{deliveryId}', function ($user, $deliveryId) {
    $delivery = \App\Models\Delivery::find($deliveryId);
    if (!$delivery) return false;
    return $user->id === $delivery->customer_id || $user->id === $delivery->courier_id || $user->role === 'admin';
});

Broadcast::channel('courier.available', function ($user) {
    return $user->role === 'courier' || $user->role === 'admin';
});

Broadcast::channel('support.tickets.{ticketId}', function ($user, $ticketId) {
    // Basic auth for now since support system is mocked
    return $user->role === 'customer' || $user->role === 'support' || $user->role === 'admin';
});

