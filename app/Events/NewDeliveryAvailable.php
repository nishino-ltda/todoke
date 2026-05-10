<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewDeliveryAvailable implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Delivery $delivery)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('courier.available'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'delivery_id' => $this->delivery->id,
            'origin' => $this->delivery->origin,
            'destination' => $this->delivery->destination,
            'value' => $this->delivery->value,
            'message' => "New delivery available from {$this->delivery->origin['address'] ?? 'Origin'}",
        ];
    }
}
